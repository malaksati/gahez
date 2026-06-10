<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Requests\Api\StoreOfferRequest;
use App\V1\Http\Requests\Api\UpdateOfferRequest;
use App\V1\Http\Resources\Api\OfferResource;
use App\V1\Services\OfferService;

class OfferController extends Controller
{
    public function __construct(
        protected OfferService $offerService,
    ) {}

    public function index()
    {
        return OfferResource::collection(
            $this->offerService->getValidOffers()
        );
    }

    public function store(StoreOfferRequest $request)
    {
        $offer = $this->offerService->create($request->validated());

        return (new OfferResource($offer->load('offerable')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateOfferRequest $request, int $id)
    {
        $offer = $this->offerService->update($id, $request->validated());

        return new OfferResource($offer->load('offerable'));
    }

    public function destroy(int $id)
    {
        $this->offerService->delete($id);

        return response()->noContent();
    }
}
