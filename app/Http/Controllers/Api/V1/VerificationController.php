<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\VerificationDocumentResource;
use App\Models\VerificationDocument;
use App\Models\VerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    /**
     * Get the user's verification status grouped by type.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        $types = ['profile', 'host_identity', 'vehicle'];
        $result = [];

        foreach ($types as $type) {
            $verificationRequest = VerificationRequest::where('user_id', $user->id)
                ->where('type', $type)
                ->withCount('documents')
                ->first();

            if ($verificationRequest) {
                $result[$type] = [
                    'id' => $verificationRequest->id,
                    'status' => $verificationRequest->status,
                    'documents_count' => $verificationRequest->documents_count,
                    'submitted_at' => $verificationRequest->submitted_at,
                    'reviewed_at' => $verificationRequest->reviewed_at,
                ];
            } else {
                $result[$type] = [
                    'id' => null,
                    'status' => 'empty',
                    'documents_count' => 0,
                    'submitted_at' => null,
                    'reviewed_at' => null,
                ];
            }
        }

        return response()->json($result);
    }

    /**
     * Upload a verification document.
     */
    public function uploadDocument(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:profile,host_identity,vehicle',
            'document_type' => 'required|in:driving_license,rc_book,insurance,vehicle_photo,profile_photo,aadhaar_reference',
            'file' => [
                'required',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf',
            ],
        ]);

        $user = $request->user();

        // Get or create verification request for this type
        $verificationRequest = VerificationRequest::firstOrCreate(
            [
                'user_id' => $user->id,
                'type' => $request->input('type'),
            ],
            [
                'status' => 'empty',
            ]
        );

        // Store file in 'verification' disk (private)
        $file = $request->file('file');
        $path = $file->store('verification', 'verification');

        // Get file MIME type
        $fileType = $file->getMimeType();

        // Create verification document record
        $document = VerificationDocument::create([
            'request_id' => $verificationRequest->id,
            'user_id' => $user->id,
            'document_type' => $request->input('document_type'),
            'file_path' => $path,
            'file_type' => $fileType,
            'status' => 'pending',
        ]);

        // Update request status to 'pending' if it was 'empty'
        if ($verificationRequest->status === 'empty') {
            $verificationRequest->update([
                'status' => 'pending',
                'submitted_at' => now(),
            ]);
        }

        return response()->json(new VerificationDocumentResource($document), 201);
    }

    /**
     * Delete a verification document.
     */
    public function destroyDocument(Request $request, VerificationDocument $document): JsonResponse
    {
        $user = $request->user();

        // Verify document belongs to user
        if ($document->verificationRequest->user_id !== $user->id) {
            abort(403, 'This document does not belong to you.');
        }

        // Delete file from storage
        if (Storage::disk('verification')->exists($document->file_path)) {
            Storage::disk('verification')->delete($document->file_path);
        }

        // Delete document record
        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully',
        ]);
    }
}
