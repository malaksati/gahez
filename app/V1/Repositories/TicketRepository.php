<?php

namespace App\V1\Repositories;

use App\Models\Ticket;
use App\V1\Repositories\Concerns\AppliesInsensitiveSearch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TicketRepository
{
    use AppliesInsensitiveSearch;

    protected $model;

    public function __construct(Ticket $ticket)
    {
        $this->model = $ticket;
    }

    public function getAllTickets(): Collection
    {
        return $this->model->with(['user', 'messages'])->latest()->all();
    }

    public function getPaginatedTickets(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['user'])->withCount('messages');

        if (! empty($filters['search'])) {
            $this->applyColumnsSearchInsensitive($query, ['subject', 'description'], (string) $filters['search']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $sort = (string) ($filters['sort'] ?? 'latest');

        match ($sort) {
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }

    public function getTicketById(int $id): Ticket
    {
        return $this->model
            ->with([
                'user',
                'messages' => fn ($query) => $query->with('sender')->oldest(),
            ])
            ->findOrFail($id);
    }

    public function getTicketsByUser(int $userId): Collection
    {
        return $this->model->with(['user', 'messages'])->where('user_id', $userId)->latest()->get();
    }

    public function getPendingTickets(): Collection
    {
        return $this->model->with(['user', 'messages'])->pending()->latest()->get();
    }

    public function getResolvedTickets(): Collection
    {
        return $this->model->with(['user', 'messages'])->resolved()->latest()->get();
    }

    public function getClosedTickets(): Collection
    {
        return $this->model->with(['user', 'messages'])->closed()->latest()->get();
    }

    public function getOpenTickets(): Collection
    {
        return $this->model->with(['user', 'messages'])->open()->latest()->get();
    }

    public function create(array $data): Ticket
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $ticketData): ?Ticket
    {
        $ticket = $this->model->findOrFail($id);
        if ($ticket) {
            $ticket->update($ticketData);

            return $ticket->fresh();
        }

        return null;
    }

    public function delete(int $id): ?bool
    {
        $ticket = $this->model->findOrFail($id);
        $ticket->messages()->delete();

        return (bool) $ticket->delete();
    }

    public function forceDelete(int $id): ?bool
    {
        $ticket = $this->model->findOrFail($id);
        if ($ticket) {
            $ticket->whenHas('messages', function ($query) {
                $query->delete();
            });

            return $ticket->forceDelete();
        }

        return null;
    }

    public function restore(int $id): ?bool
    {
        $ticket = $this->model->findOrFail($id);
        if ($ticket) {
            return $ticket->restore();
        }

        return null;
    }
}
