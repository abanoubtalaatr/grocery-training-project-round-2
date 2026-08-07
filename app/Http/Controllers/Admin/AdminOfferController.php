<?php

namespace App\Http\Controllers\Admin;

use App\Action\Admin\Offer\CreateOfferAction;
use App\Action\Admin\Offer\DeleteOfferAction;
use App\Action\Admin\Offer\ToggleOfferStatusAction;
use App\Action\Admin\Offer\UpdateOfferAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOfferRequest;
use App\Http\Requests\Admin\UpdateOfferRequest;
use App\Http\Resources\Admin\OfferResource;
use App\Models\Offer;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AdminOfferController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $offers = Offer::latest()->get();

        return $this->success(OfferResource::collection($offers),'Offers retrieved successfully');
    }

    public function store(StoreOfferRequest $request, CreateOfferAction $action): JsonResponse
    {
        $offer = $action->execute($request->validated());

        return $this->success(new OfferResource($offer),'Offer created successfully',201);
    }

    public function show(Offer $offer): JsonResponse
    {
        return $this->success(new OfferResource($offer),'Offer retrieved successfully');
    }

    public function update(UpdateOfferRequest $request, Offer $offer, UpdateOfferAction $action): JsonResponse
    {
        $action->execute($offer, $request->validated());

        return $this->success( new OfferResource($offer->fresh()),'Offer updated successfully');
    }

    public function destroy(Offer $offer, DeleteOfferAction $action): JsonResponse
    {
        $action->execute($offer);

        return $this->success(null, 'Offer deleted successfully');
    }

    public function toggleStatus(Offer $offer, ToggleOfferStatusAction $action): JsonResponse
    {
        $action->execute($offer);

        return $this->success(new OfferResource($offer->fresh()),'Offer status updated successfully');
    }
}
