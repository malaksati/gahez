<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Requests\Api\StoreAddressRequest;
use App\V1\Http\Requests\Api\UpdateAddressRequest;
use App\V1\Http\Resources\Api\AddressResource;
use App\V1\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(
        protected AddressService $addressService,
    ) {}

    public function index(Request $request)
    {
        return AddressResource::collection(
            $this->addressService->allByUser($request->user()->id)
        );
    }

    public function show(Request $request, int $id)
    {
        return new AddressResource(
            $this->addressService->findById($id, $request->user()->id)
        );
    }

    public function store(StoreAddressRequest $request)
    {
        $address = $this->addressService->create(
            $request->user()->id,
            $request->validated()
        );

        return (new AddressResource($address))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateAddressRequest $request, int $id)
    {
        return new AddressResource(
            $this->addressService->update($id, $request->user()->id, $request->validated())
        );
    }

    public function destroy(UpdateAddressRequest $request, int $id): JsonResponse
    {
        $this->addressService->delete($id, $request->user()->id);

        return response()->json([
            'message' => 'Address deleted successfully.',
        ]);
    }
}
