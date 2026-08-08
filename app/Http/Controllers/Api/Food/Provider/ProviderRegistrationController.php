<?php

namespace App\Http\Controllers\Api\Food\Provider;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProviderRegistrationController extends Controller
{
    /**
     * POST /food/provider/register — Create a new food provider profile.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider_type'    => 'required|string|in:homemade,catering,hotel',
            'business_name'    => 'required|string|max:255',
            'description'      => 'nullable|string|max:1000',
            'phone'            => 'required|string|max:20',
            'email'            => 'required|email|max:255',
            'address'          => 'required|string|max:500',
            'latitude'         => 'required|numeric|between:-90,90',
            'longitude'        => 'required|numeric|between:-180,180',
            'city'             => 'required|string|max:100',
            'state'            => 'required|string|max:100',
            'pincode'          => 'required|string|max:10',
            'fssai_license'    => 'nullable|string|max:50',
            'fssai_expiry'     => 'nullable|date|after:today',
            'gst_number'       => 'nullable|string|max:20',
            'pan_number'       => 'nullable|string|max:20',
        ]);

        // Check if user already has a provider profile
        $existingProvider = FoodProvider::where('user_id', $request->user()->id)->first();

        if ($existingProvider) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a provider profile.',
                'data'    => ['provider' => $existingProvider],
            ], 409);
        }

        $validated['user_id'] = $request->user()->id;
        $validated['verification_status'] = 'pending';
        $validated['is_active'] = false;
        $validated['avg_rating'] = 0.00;
        $validated['total_orders'] = 0;
        $validated['total_revenue'] = 0.00;
        $validated['commission_rate'] = 15.00;
        $validated['delivery_radius_km'] = 10;
        $validated['min_order_amount'] = 0.00;
        $validated['free_delivery_above'] = 0.00;

        $provider = FoodProvider::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Provider registration submitted. Your profile is pending verification.',
            'data'    => ['provider' => $provider],
        ], 201);
    }

    /**
     * PUT /food/provider/profile — Update provider profile fields.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $provider = FoodProvider::where('user_id', $request->user()->id)->firstOrFail();

        $validated = $request->validate([
            'provider_type'    => 'sometimes|string|in:homemade,catering,hotel',
            'business_name'    => 'sometimes|string|max:255',
            'description'      => 'nullable|string|max:1000',
            'logo_url'         => 'nullable|url|max:500',
            'cover_image_url'  => 'nullable|url|max:500',
            'phone'            => 'sometimes|string|max:20',
            'email'            => 'sometimes|email|max:255',
            'address'          => 'sometimes|string|max:500',
            'latitude'         => 'sometimes|numeric|between:-90,90',
            'longitude'        => 'sometimes|numeric|between:-180,180',
            'city'             => 'sometimes|string|max:100',
            'state'            => 'sometimes|string|max:100',
            'pincode'          => 'sometimes|string|max:10',
            'fssai_license'    => 'nullable|string|max:50',
            'fssai_expiry'     => 'nullable|date',
            'gst_number'       => 'nullable|string|max:20',
            'pan_number'       => 'nullable|string|max:20',
            'operating_hours'  => 'nullable|array',
            'delivery_radius_km'    => 'nullable|integer|min:1|max:50',
            'min_order_amount'      => 'nullable|numeric|min:0',
            'free_delivery_above'   => 'nullable|numeric|min:0',
            'bank_account_number'   => 'nullable|string|max:30',
            'bank_ifsc'             => 'nullable|string|max:20',
            'upi_id'                => 'nullable|string|max:100',
        ]);

        $provider->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => ['provider' => $provider->fresh()],
        ]);
    }

    /**
     * POST /food/provider/documents — Upload verification documents.
     */
    public function uploadDocuments(Request $request): JsonResponse
    {
        $provider = FoodProvider::where('user_id', $request->user()->id)->firstOrFail();

        $validated = $request->validate([
            'documents'           => 'required|array|min:1|max:10',
            'documents.*'         => 'required|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'document_type'       => 'required|string|in:fssai,gst,pan,aadhar,trade_license,shop_photo,other',
            'description'         => 'nullable|string|max:255',
        ]);

        $uploadedFiles = [];

        foreach ($request->file('documents') as $index => $file) {
            $path = $file->store('provider-documents/' . $provider->id, 'public');
            $uploadedFiles[] = [
                'type'        => $validated['document_type'],
                'path'        => $path,
                'filename'    => $file->getClientOriginalName(),
                'mime_type'   => $file->getMimeType(),
                'size'        => $file->getSize(),
                'description' => $validated['description'] ?? null,
                'uploaded_at' => now()->toISOString(),
            ];
        }

        // Append to existing documents (store as JSON)
        $existingDocs = json_decode($provider->documents ?? '[]', true) ?? [];
        $existingDocs = array_merge($existingDocs, $uploadedFiles);

        $provider->update([
            'documents' => json_encode($existingDocs),
        ]);

        return response()->json([
            'success' => true,
            'message' => count($uploadedFiles) . ' document(s) uploaded successfully.',
            'data'    => [
                'provider'  => $provider->fresh(),
                'documents' => $existingDocs,
            ],
        ]);
    }

    /**
     * GET /food/provider/profile — Return current user's provider profile.
     */
    public function getProfile(Request $request): JsonResponse
    {
        $provider = FoodProvider::where('user_id', $request->user()->id)
            ->withCount(['items', 'orders', 'reviews'])
            ->with([
                'user:id,name,phone,email,avatar',
            ])
            ->first();

        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'No provider profile found. Please register first.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => ['provider' => $provider],
        ]);
    }
}
