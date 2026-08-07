<?php

namespace App\Http\Controllers\Admin;

use App\Action\Admin\Contact\GetContactStatsAction;
use App\Action\Admin\Contact\GetContactsAction;
use App\Action\Admin\Contact\MarkContactAsReadAction;
use App\Action\Admin\Contact\MarkContactAsRepliedAction;
use App\Action\Admin\Contact\MarkContactAsSpamAction;
use App\Action\Admin\Contact\DeleteContactAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ContactMessageResource;
use App\Models\ContactMessage;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminContactController extends Controller
{
    use ApiResponse;

    public function index(Request $request, GetContactsAction $action): JsonResponse
    {
        $result = $action->execute($request->all());

        return $this->success(
            [
                'contacts' => ContactMessageResource::collection($result['contacts']),
                'pagination' => $result['pagination'],
            ],
            'Contact messages retrieved successfully'
        );
    }

    public function show(ContactMessage $contact): JsonResponse
    {
        return $this->success(new ContactMessageResource($contact),'Contact message retrieved successfully');
    }

    public function markAsRead(ContactMessage $contact, MarkContactAsReadAction $action): JsonResponse
    {
        $action->execute($contact);

        return $this->success(new ContactMessageResource($contact->fresh()),'Message marked as read');
    }

    public function markAsReplied(ContactMessage $contact, MarkContactAsRepliedAction $action): JsonResponse
    {
        $action->execute($contact);

        return $this->success( new ContactMessageResource($contact->fresh()),'Message marked as replied');
    }

    public function markAsSpam(ContactMessage $contact, MarkContactAsSpamAction $action): JsonResponse
    {
        $action->execute($contact);

        return $this->success(new ContactMessageResource($contact->fresh()),'Message marked as spam');
    }

    public function destroy(ContactMessage $contact, DeleteContactAction $action): JsonResponse
    {
        $action->execute($contact);

        return $this->success(null, 'Message deleted successfully');
    }

    public function stats(GetContactStatsAction $action): JsonResponse
    {
        $stats = $action->execute();

        return $this->success($stats, 'Contact statistics retrieved successfully');
    }
}
