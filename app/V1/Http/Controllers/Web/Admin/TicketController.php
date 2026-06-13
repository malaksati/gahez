<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\Ticket;
use App\V1\Http\Requests\Web\Admin\StoreTicketMessageRequest;
use App\V1\Http\Requests\Web\Admin\UpdateTicketRequest;
use App\V1\Http\Requests\Web\Admin\UpdateTicketStatusRequest;
use App\V1\Services\TicketService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TicketController extends AdminController
{
    public function __construct(
        protected TicketService $tickets,
    ) {}

    public function index(Request $request): View|Response
    {
        $tickets = $this->tickets->getPaginatedTickets(15, $this->listFilters($request, [
            'search', 'status', 'type', 'from_date', 'to_date', 'sort',
        ]));

        return $this->adminListResponse(
            $request,
            'v1.admin.tickets.index',
            'v1.admin.tickets.partials.results',
            ['tickets' => $tickets],
            ['tickets' => $tickets],
        );
    }

    public function show(Ticket $ticket): View
    {
        return view('v1.admin.tickets.show', [
            'ticket' => $this->tickets->getTicketById($ticket->id),
        ]);
    }

    public function edit(Ticket $ticket): View
    {
        return view('v1.admin.tickets.edit', [
            'ticket' => $this->tickets->getTicketById($ticket->id),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->tickets->update($ticket->id, $request->validated());

        return redirect()
            ->route('v1.admin.tickets.show', $ticket)
            ->with('success', __('messages.Ticket updated successfully.'));
    }

    public function storeMessage(StoreTicketMessageRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->tickets->addMessage($ticket->id, [
            'message' => $request->validated('message'),
            'sender_type' => 'admin',
            'sender_id' => $request->user()->id,
            'attachments' => $request->ticketAttachmentFiles(),
        ]);

        return redirect()
            ->route('v1.admin.tickets.show', $ticket)
            ->with('success', __('messages.Message added successfully.'));
    }

    public function updateStatus(UpdateTicketStatusRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->tickets->updateStatus($ticket->id, $request->validated('status'));

        return redirect()
            ->route('v1.admin.tickets.show', $ticket)
            ->with('success', __('messages.Ticket status updated successfully.'));
    }
}
