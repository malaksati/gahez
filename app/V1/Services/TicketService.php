<?php

namespace App\V1\Services;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketMessageAddedNotification;
use App\V1\Repositories\TicketRepository;
use App\V1\Support\UploadStorage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketService
{
    public function __construct(
        protected TicketRepository $tickets,
        protected NotificationService $notifications,
    ) {}

    public function getAllTickets(): Collection
    {
        return $this->tickets->getAllTickets();
    }

    public function getPaginatedTickets(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->tickets->getPaginatedTickets($perPage, $filters);
    }

    public function getTicketById(int $id): Ticket
    {
        return $this->tickets->getTicketById($id);
    }

    public function getTicketsByUser(int $userId): Collection
    {
        return $this->tickets->getTicketsByUser($userId);
    }

    public function getPendingTickets(): Collection
    {
        return $this->tickets->getPendingTickets();
    }

    public function getResolvedTickets(): Collection
    {
        return $this->tickets->getResolvedTickets();
    }

    public function getClosedTickets(): Collection
    {
        return $this->tickets->getClosedTickets();
    }

    public function getOpenTickets(): Collection
    {
        return $this->tickets->getOpenTickets();
    }

    public function create(array $data): Ticket
    {
        return DB::transaction(function () use ($data) {
            $attachments = $this->storeUploadedFiles($data['attachments'] ?? null, 'tickets');
            unset($data['attachments']);

            $ticket = $this->tickets->create([
                ...$data,
                'attachments' => $attachments !== [] ? $attachments : null,
            ]);

            if (! empty($data['description'])) {
                TicketMessage::query()->create([
                    'ticket_id' => $ticket->id,
                    'sender_type' => 'user',
                    'sender_id' => $ticket->user_id,
                    'message' => $data['description'],
                    'attachments' => $attachments !== [] ? $attachments : null,
                ]);
            }

            $ticket = $ticket->fresh(['user', 'messages.sender']);
            $this->notifications->notifyAdmins(new TicketCreatedNotification($ticket));

            return $ticket;
        });
    }

    public function update(int $id, array $data): Ticket
    {
        return $this->tickets->update($id, $data);
    }

    public function updateStatus(int $id, string $status): Ticket
    {
        if (! in_array($status, ['pending', 'resolved', 'closed'], true)) {
            throw ValidationException::withMessages([
                'status' => ['Invalid ticket status.'],
            ]);
        }

        return $this->tickets->update($id, ['status' => $status]);
    }

    public function addMessage(int $ticketId, array $messageData): TicketMessage
    {
        return DB::transaction(function () use ($ticketId, $messageData) {
            $ticket = $this->tickets->getTicketById($ticketId);

            if ($ticket->status === 'closed') {
                throw ValidationException::withMessages([
                    'message' => ['This ticket is closed. Change the status before sending a message.'],
                ]);
            }

            $attachments = $this->storeUploadedFiles($messageData['attachments'] ?? null, 'tickets/messages');
            unset($messageData['attachments']);

            $message = TicketMessage::query()->create([
                'ticket_id' => $ticketId,
                'sender_type' => $messageData['sender_type'] ?? 'user',
                'sender_id' => $messageData['sender_id'],
                'message' => $messageData['message'],
                'attachments' => $attachments !== [] ? $attachments : null,
            ]);

            if (in_array($ticket->status, ['resolved', 'closed'], true)) {
                $ticket->update(['status' => 'pending']);
            }

            $message->load('sender');
            $ticket = $ticket->fresh(['user', 'messages.sender']);

            $notification = new TicketMessageAddedNotification($ticket, $message);

            if (($message->sender_type ?? 'user') === 'user') {
                $this->notifications->notifyAdmins($notification);
            } else {
                $this->notifications->notifyUser($ticket->user, $notification);
            }

            return $message;
        });
    }

    public function delete(int $id): bool
    {
        return $this->tickets->delete($id);
    }

    public function forceDelete(int $id): bool
    {
        return $this->tickets->forceDelete($id);
    }

    public function restore(int $id): bool
    {
        return $this->tickets->restore($id);
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
                    'attachments' => ['Failed to upload attachment. Please try again.'],
                ]);
            }
        }

        return $paths;
    }
}
