<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EmergencyContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmergencyContactController extends Controller
{
    /**
     * GET /emergency-contacts — List user's emergency contacts.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $contacts = $request->user()
            ->emergencyContacts()
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $contacts,
        ]);
    }

    /**
     * POST /emergency-contacts — Add a new emergency contact.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'relation' => 'required|string|max:100',
        ]);

        // Limit to 5 emergency contacts per user
        $count = $request->user()->emergencyContacts()->count();
        if ($count >= 5) {
            return response()->json([
                'message' => 'Maximum of 5 emergency contacts allowed.',
            ], 422);
        }

        $contact = $request->user()->emergencyContacts()->create($validated);

        return response()->json([
            'message' => 'Emergency contact added successfully.',
            'data' => $contact,
        ], 201);
    }

    /**
     * GET /emergency-contacts/{contact} — Show a specific emergency contact.
     */
    public function show(Request $request, EmergencyContact $contact): JsonResponse
    {
        if ($contact->user_id !== $request->user()->id) {
            abort(403, 'This contact does not belong to you.');
        }

        return response()->json([
            'data' => $contact,
        ]);
    }

    /**
     * PATCH /emergency-contacts/{contact} — Update an emergency contact.
     */
    public function update(Request $request, EmergencyContact $contact): JsonResponse
    {
        if ($contact->user_id !== $request->user()->id) {
            abort(403, 'This contact does not belong to you.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'relation' => 'sometimes|string|max:100',
        ]);

        $contact->update($validated);

        return response()->json([
            'message' => 'Emergency contact updated successfully.',
            'data' => $contact,
        ]);
    }

    /**
     * DELETE /emergency-contacts/{contact} — Remove an emergency contact.
     */
    public function destroy(Request $request, EmergencyContact $contact): JsonResponse
    {
        if ($contact->user_id !== $request->user()->id) {
            abort(403, 'This contact does not belong to you.');
        }

        $contact->delete();

        return response()->json([
            'message' => 'Emergency contact removed successfully.',
        ]);
    }
}
