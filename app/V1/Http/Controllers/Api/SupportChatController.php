<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Support;
use App\V1\Http\Requests\Api\StoreSupportChatRequest;
use App\V1\Http\Requests\Api\StoreSupportMessageRequest;
use App\V1\Http\Resources\Api\SupportChatResource;
use App\V1\Http\Resources\Api\SupportMessageResource;
use App\V1\Services\SupportChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function __construct(
        protected SupportChatService $supportChats,
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 15);

        return SupportChatResource::collection(
            $this->supportChats->getPaginatedForUser($request->user()->id, $perPage),
        );
    }

    public function store(StoreSupportChatRequest $request)
    {
        $support = $this->supportChats->createConversation($request->user()->id, [
            ...$request->validated(),
            'attachments' => $request->supportAttachmentFiles(),
        ]);

        return (new SupportChatResource($support))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Support $support)
    {
        $this->authorize('view', $support);

        return new SupportChatResource(
            $this->supportChats->getById($support->id),
        );
    }

    public function messages(Request $request, Support $support)
    {
        $this->authorize('view', $support);

        $perPage = (int) $request->integer('per_page', 30);

        return SupportMessageResource::collection(
            $this->supportChats->getPaginatedMessages($support->id, $perPage),
        );
    }

    public function storeMessage(StoreSupportMessageRequest $request, Support $support): JsonResponse
    {
        $this->authorize('sendMessage', $support);

        $message = $this->supportChats->addMessage($support->id, [
            'message' => $request->validated('message'),
            'sender_type' => 'user',
            'sender_id' => $request->user()->id,
            'attachments' => $request->supportAttachmentFiles(),
        ]);

        return (new SupportMessageResource($message))
            ->response()
            ->setStatusCode(201);
    }
}
