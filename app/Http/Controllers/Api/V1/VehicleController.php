<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreVehicleRequest;
use App\Http\Requests\Api\V1\UpdateVehicleRequest;
use App\Http\Resources\Api\V1\VehicleResource;
use App\Models\Vehicle;
use App\Models\VerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VehicleController extends Controller
{
    /**
     * Display the authenticated user's vehicles.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Vehicle::class);

        $vehicles = $request->user()
            ->vehicles()
            ->with('category')
            ->get();

        return VehicleResource::collection($vehicles);
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $this->authorize('create', Vehicle::class);

        $vehicle = $request->user()->vehicles()->create($request->validated());
        $vehicle->load('category');

        return response()->json(new VehicleResource($vehicle), 201);
    }

    /**
     * Display the specified vehicle.
     */
    public function show(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('view', $vehicle);

        $vehicle->load(['category', 'documents']);

        return response()->json(new VehicleResource($vehicle));
    }

    /**
     * Update the specified vehicle.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorize('update', $vehicle);

        $vehicle->update($request->validated());
        $vehicle->load('category');

        return response()->json(new VehicleResource($vehicle));
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('delete', $vehicle);

        $vehicle->delete();

        return response()->json([
            'message' => 'Vehicle deleted successfully',
        ]);
    }

    /**
     * Submit a vehicle for verification.
     */
    public function submitVerification(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('submitVerification', $vehicle);

        // Check vehicle has required documents (rc_book, insurance)
        $requiredDocuments = ['rc_book', 'insurance'];
        $existingDocumentTypes = $vehicle->documents()->pluck('document_type')->toArray();

        $missingDocuments = array_diff($requiredDocuments, $existingDocumentTypes);

        if (! empty($missingDocuments)) {
            return response()->json([
                'message' => 'Vehicle must have required documents before submission.',
                'missing_documents' => array_values($missingDocuments),
            ], 422);
        }

        // Get or create verification request for this vehicle
        $verificationRequest = VerificationRequest::firstOrCreate(
            [
                'user_id' => $vehicle->user_id,
                'type' => 'vehicle',
                'vehicle_id' => $vehicle->id,
            ],
            [
                'status' => 'pending',
                'submitted_at' => now(),
            ]
        );

        // Update verification request status if not already pending
        if ($verificationRequest->status !== 'pending') {
            $verificationRequest->update([
                'status' => 'pending',
                'submitted_at' => now(),
            ]);
        }

        // Update vehicle verification status
        $vehicle->update([
            'verification_status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Vehicle submitted for verification successfully',
            'verification_request' => [
                'id' => $verificationRequest->id,
                'type' => $verificationRequest->type,
                'status' => $verificationRequest->status,
                'submitted_at' => $verificationRequest->submitted_at,
            ],
        ]);
    }
}
