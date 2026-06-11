<?php

namespace App\V1\Repositories;

use App\Models\Support;
use App\Models\SupportMessage;
use App\V1\Repositories\Concerns\AppliesInsensitiveSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SupportRepository
{
    use AppliesInsensitiveSearch;

    public function __construct(protected Support $model) {}

    public function getById(int $id): Support
    {
        return $this->model->newQuery()
            ->with(['user', 'assignedAdmin'])
            ->findOrFail($id);
    }

    public function getPaginatedForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['assignedAdmin', 'latestMessage.sender'])
            ->withCount([
                'messages as unread_messages_count' => fn ($query) => $query
                    ->where('sender_type', 'admin')
                    ->whereNull('read_at'),
            ])
            ->where('user_id', $userId)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedForAdmin(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['user', 'assignedAdmin', 'latestMessage.sender']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['assigned_admin_id'])) {
            $query->where('assigned_admin_id', (int) $filters['assigned_admin_id']);
        }

        if (filter_var($filters['unassigned'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNull('assigned_admin_id');
        }

        if (! empty($filters['search'])) {
            $term = $this->insensitiveLikeTerm((string) $filters['search']);
            $query->where(function (Builder $builder) use ($term) {
                $builder->where('subject', 'like', $term)
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term));
            });
        }

        return $query
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function getPaginatedMessages(int $supportId, int $perPage = 30): LengthAwarePaginator
    {
        return SupportMessage::query()
            ->where('support_id', $supportId)
            ->with('sender')
            ->orderBy('created_at')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Support
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Support $support, array $data): Support
    {
        $support->update($data);

        return $support->fresh(['user', 'assignedAdmin']);
    }
}
