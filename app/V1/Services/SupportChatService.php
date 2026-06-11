<?php

namespace App\V1\Services;

use App\Events\SupportMessageSent;
use App\Models\Support;
use App\Models\SupportMessage;
use App\Models\User;
use App\Notifications\SupportMessageFromAdminNotification;
use App\Notifications\SupportMessageFromCustomerNotification;
use App\V1\Repositories\SupportRepository;
use App\V1\Support\UploadStorage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupportChatService
{
    public function __construct(
        protected SupportRepository $supports,
        protected NotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedForAdmin(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->supports->getPaginatedForAdmin($perPage, $filters);
    }

    public function getPaginatedForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->supports->getPaginatedForUser($userId, $perPage);
    }

    public function getById(int $id): Support
    {
        return $this->supports->getById($id);
    }

    public function getPaginatedMessages(int $supportId, int $perPage = 30): LengthAwarePaginator
    {
        return $this->supports->getPaginatedMessages($supportId, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createConversation(int $userId, array $data): Support
    {
        $initialMessage = trim((string) ($data['message'] ?? ''));
        $attachments = $this->storeUploadedFiles($data['attachments'] ?? null, 'support/messages');

        [$support, $message] = DB::transaction(function () use ($userId, $data, $initialMessage, $attachments) {
            $support = $this->supports->create([
                'user_id' => $userId,
                'status' => 'open',
                'subject' => $data['subject'] ?? null,
                'last_message_at' => $initialMessage !== '' ? now() : null,
            ]);

            if ($initialMessage === '') {
                return [$support, null];
            }

            $message = $this->persistMessage($support, [
                'message' => $initialMessage,
                'sender_type' => 'user',
                'sender_id' => $userId,
                'attachments' => $attachments,
            ]);

            return [$support, $message];
        });

        if ($message !== null) {
            $this->dispatchNotifications($support, $message);
        }

        return $support->fresh(['user', 'assignedAdmin', 'latestMessage.sender']);
    }

    public function assignAdmin(int $supportId, int $adminId): Support
    {
        $support = $this->supports->getById($supportId);

        if (! User::query()->whereKey($adminId)->where('role', 'admin')->exists()) {
            throw ValidationException::withMessages([
                'assigned_admin_id' => [__('messages.Selected admin is invalid.')],
            ]);
        }

        return $this->supports->update($support, [
            'assigned_admin_id' => $adminId,
        ]);
    }

    public function closeConversation(int $supportId): Support
    {
        $support = $this->supports->getById($supportId);

        return $this->supports->update($support, [
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    public function reopenConversation(int $supportId): Support
    {
        $support = $this->supports->getById($supportId);

        return $this->supports->update($support, [
            'status' => 'open',
            'closed_at' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $messageData
     */
    public function addMessage(int $supportId, array $messageData): SupportMessage
    {
        $message = DB::transaction(function () use ($supportId, $messageData) {
            $support = $this->supports->getById($supportId);

            if ($support->isClosed()) {
                throw ValidationException::withMessages([
                    'message' => [__('messages.Support chat is closed.')],
                ]);
            }

            $attachments = $this->storeUploadedFiles($messageData['attachments'] ?? null, 'support/messages');
            unset($messageData['attachments']);

            $senderType = (string) ($messageData['sender_type'] ?? 'user');
            $senderId = (int) $messageData['sender_id'];

            if ($senderType === 'admin' && ! $support->assigned_admin_id) {
                $this->supports->update($support, ['assigned_admin_id' => $senderId]);
                $support->assigned_admin_id = $senderId;
            }

            return $this->persistMessage($support, [
                'message' => $messageData['message'],
                'sender_type' => $senderType,
                'sender_id' => $senderId,
                'attachments' => $attachments,
            ]);
        });

        $this->dispatchNotifications(
            $this->supports->getById($supportId),
            $message,
        );

        return $message;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function persistMessage(Support $support, array $payload): SupportMessage
    {
        $message = SupportMessage::query()->create([
            'support_id' => $support->id,
            'sender_type' => $payload['sender_type'],
            'sender_id' => $payload['sender_id'],
            'message' => $payload['message'],
            'attachments' => ! empty($payload['attachments']) ? $payload['attachments'] : null,
        ]);

        $support->update(['last_message_at' => now()]);
        $message->load('sender');

        SupportMessageSent::dispatch($message);

        return $message;
    }

    protected function dispatchNotifications(Support $support, SupportMessage $message): void
    {
        $support = $support->fresh(['user', 'assignedAdmin']);

        if ($message->sender_type === 'user') {
            $notification = new SupportMessageFromCustomerNotification($support, $message);

            if ($support->assigned_admin_id) {
                $assigned = User::query()->find($support->assigned_admin_id);
                $this->notifications->notifyUser($assigned, $notification);
            } else {
                $this->notifications->notifyAdminsWithPermission('manage support-chats', $notification);
            }

            return;
        }

        $this->notifications->notifyUser(
            $support->user,
            new SupportMessageFromAdminNotification($support, $message),
        );
    }

    /**
     * @param  array<int, UploadedFile>|UploadedFile|null  $files
     * @return list<string>
     */
    protected function storeUploadedFiles(mixed $files, string $directory): array
    {
        if ($files === null) {
            return [];
        }

        $files = is_array($files) ? $files : [$files];
        $paths = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            try {
                $paths[] = UploadStorage::store($file, $directory);
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'attachments' => [__('messages.Failed to upload attachment.')],
                ]);
            }
        }

        return $paths;
    }
}
