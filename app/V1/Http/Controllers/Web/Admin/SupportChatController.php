<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\Support;
use App\Models\User;
use App\V1\Http\Requests\Web\Admin\AssignSupportChatRequest;
use App\V1\Http\Requests\Web\Admin\StoreSupportMessageRequest;
use App\V1\Http\Requests\Web\Admin\UpdateSupportChatStatusRequest;
use App\V1\Http\Resources\Api\SupportMessageResource;
use App\V1\Services\SupportChatService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SupportChatController extends AdminController
{
    public function __construct(
        protected SupportChatService $supportChats,
    ) {}

    public function index(Request $request): View|Response
    {
        $this->authorize('viewAny', Support::class);

        $supports = $this->supportChats->getPaginatedForAdmin(
            15,
            $this->listFilters($request, [
                'search', 'status', 'assigned_admin_id', 'unassigned',
            ]),
        );

        return $this->adminListResponse(
            $request,
            'v1.admin.support-chats.index',
            'v1.admin.support-chats.partials.results',
            [
                'supports' => $supports,
                'admins' => $this->adminOptions(),
            ],
            [
                'supports' => $supports,
            ],
        );
    }

    public function show(Support $support): View
    {
        $this->authorize('view', $support);

        return view('v1.admin.support-chats.show', [
            'support' => $this->supportChats->getById($support->id),
            'messages' => $this->supportChats->getPaginatedMessages($support->id, 50),
            'admins' => $this->adminOptions(),
        ]);
    }

    public function messages(Request $request, Support $support): JsonResponse
    {
        $this->authorize('view', $support);

        $perPage = min((int) $request->integer('per_page', 50), 100);

        return SupportMessageResource::collection(
            $this->supportChats->getPaginatedMessages($support->id, $perPage),
        )->response();
    }

    public function storeMessage(StoreSupportMessageRequest $request, Support $support): RedirectResponse|JsonResponse
    {
        $this->authorize('sendMessage', $support);

        $message = $this->supportChats->addMessage($support->id, [
            'message' => $request->validated('message'),
            'sender_type' => 'admin',
            'sender_id' => $request->user()->id,
            'attachments' => $request->supportAttachmentFiles(),
        ]);

        if ($request->expectsJson() || $request->header('X-Support-Chat-Ajax')) {
            return (new SupportMessageResource($message))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()
            ->route('v1.admin.support-chats.show', $support)
            ->with('success', __('messages.Message added successfully.'));
    }

    public function assign(AssignSupportChatRequest $request, Support $support): RedirectResponse
    {
        $this->authorize('update', $support);

        $this->supportChats->assignAdmin($support->id, (int) $request->validated('assigned_admin_id'));

        return redirect()
            ->route('v1.admin.support-chats.show', $support)
            ->with('success', __('messages.Support chat assigned successfully.'));
    }

    public function updateStatus(UpdateSupportChatStatusRequest $request, Support $support): RedirectResponse
    {
        $this->authorize('update', $support);

        $status = $request->validated('status');

        if ($status === 'closed') {
            $this->supportChats->closeConversation($support->id);
        } else {
            $this->supportChats->reopenConversation($support->id);
        }

        return redirect()
            ->route('v1.admin.support-chats.show', $support)
            ->with('success', __('messages.Support chat status updated successfully.'));
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    protected function adminOptions()
    {
        return User::query()
            ->where('role', 'admin')
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
