<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Requests\Api\StoreTicketMessageRequest;
use App\V1\Http\Requests\Api\StoreTicketRequest;
use App\V1\Http\Requests\Api\UpdateTicketRequest;
use App\V1\Http\Resources\Api\TicketMessageResource;
use App\V1\Http\Resources\Api\TicketResource;
use App\V1\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        protected TicketService $ticketService,
    ) {}

    public function index(Request $request)
    {
        return TicketResource::collection(
            $this->ticketService->getTicketsByUser($request->user()->id)
        );
    }

    public function show(Request $request, int $id)
    {
        $ticket = $this->ticketService->getTicketById($id);

        abort_unless($ticket->user_id === $request->user()->id, 404);

        return new TicketResource($ticket);
    }

    public function store(StoreTicketRequest $request)
    {
        $ticket = $this->ticketService->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'status' => 'pending',
            'attachments' => $request->ticketAttachmentFiles(),
        ]);

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateTicketRequest $request, int $id)
    {
        $ticket = $this->ticketService->getTicketById($id);

        abort_unless($ticket->user_id === $request->user()->id, 404);

        $ticket = $this->ticketService->update($id, $request->validated());

        return new TicketResource($this->ticketService->getTicketById($ticket->id));
    }

    public function storeMessage(StoreTicketMessageRequest $request, int $id): JsonResponse
    {
        $ticket = $this->ticketService->getTicketById($id);

        abort_unless($ticket->user_id === $request->user()->id, 404);

        $message = $this->ticketService->addMessage($id, [
            'message' => $request->validated('message'),
            'sender_type' => 'user',
            'sender_id' => $request->user()->id,
            'attachments' => $request->ticketAttachmentFiles(),
        ]);

        return (new TicketMessageResource($message))
            ->response()
            ->setStatusCode(201);
    }
}
