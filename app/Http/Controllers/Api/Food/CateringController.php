<?php

namespace App\Http\Controllers\Api\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\CateringRequest;
use App\Services\Food\CateringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CateringController extends Controller
{
    public function __construct(
        private readonly CateringService $cateringService,
    ) {}

    /**
     * POST /food/catering — Create a catering request.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_type'           => 'required|string|max:100',
            'guest_count'          => 'required|integer|min:1',
            'event_date'           => 'required|date|after_or_equal:today',
            'event_time'           => 'nullable|string|max:20',
            'venue_address'        => 'nullable|string|max:500',
            'venue_lat'            => 'nullable|numeric|between:-90,90',
            'venue_lng'            => 'nullable|numeric|between:-180,180',
            'budget_min'           => 'nullable|numeric|min:0',
            'budget_max'           => 'nullable|numeric|min:0|gte:budget_min',
            'dietary_requirements' => 'nullable|string|max:500',
            'notes'                => 'nullable|string|max:1000',
        ]);

        $userId = $request->user()->id;
        $cateringRequest = $this->cateringService->createRequest($userId, $validated);

        return response()->json([
            'success' => true,
            'data'    => $cateringRequest,
        ], 201);
    }

    /**
     * GET /food/catering — User's catering requests.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status'   => 'nullable|in:open,accepted,completed,cancelled',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $userId = $request->user()->id;

        $query = CateringRequest::where('user_id', $userId)
            ->with(['quotes.provider:id,business_name,logo_url,rating']);

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $perPage = (int) ($validated['per_page'] ?? 20);
        $requests = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $requests->getCollection(),
            'meta'    => [
                'current_page' => $requests->currentPage(),
                'last_page'    => $requests->lastPage(),
                'per_page'     => $requests->perPage(),
                'total'        => $requests->total(),
            ],
        ]);
    }

    /**
     * GET /food/catering/{id} — Catering request detail with quotes.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $cateringRequest = CateringRequest::where('id', $id)
            ->where('user_id', $userId)
            ->with([
                'quotes.provider:id,business_name,logo_url,avg_rating,phone',
                'provider:id,business_name,logo_url,avg_rating',
            ])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $cateringRequest,
        ]);
    }

    /**
     * POST /food/catering/{id}/select-quote/{quoteId} — Accept a catering quote.
     */
    public function selectQuote(Request $request, int $id, int $quoteId): JsonResponse
    {
        $userId = $request->user()->id;

        $cateringRequest = $this->cateringService->acceptQuote($id, $quoteId, $userId);

        return response()->json([
            'success' => true,
            'data'    => $cateringRequest,
        ]);
    }

    /**
     * POST /food/catering/{id}/cancel — Cancel a catering request.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $userId = $request->user()->id;

        $cateringRequest = $this->cateringService->cancelRequest(
            $id,
            $validated['reason'],
            $userId
        );

        return response()->json([
            'success' => true,
            'data'    => $cateringRequest,
        ]);
    }
}
