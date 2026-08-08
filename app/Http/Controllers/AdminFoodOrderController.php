<?php

namespace App\Http\Controllers;

use App\Models\Food\FoodOrder;
use App\Models\Food\FoodProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminFoodOrderController extends Controller
{
    /**
     * GET /admin/food/orders — List all food orders.
     */
    public function index(Request $request)
    {
        $query = FoodOrder::with([
            'user:id,name,email,phone',
            'provider:id,business_name,city',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        if ($request->filled('delivery_type')) {
            $query->where('delivery_type', $request->delivery_type);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('provider', fn ($q2) => $q2->where('business_name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return Inertia::render('admin/food/orders/index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'order_type', 'delivery_type', 'date_from', 'date_to', 'search']),
        ]);
    }

    /**
     * GET /admin/food/orders/{order} — Show order details.
     */
    public function show(FoodOrder $order)
    {
        $order->load([
            'user:id,name,email,phone',
            'provider:id,business_name,city,phone,email',
            'items' => function ($q) {
                $q->with('foodItem:id,name,image_url');
            },
            'deliverySlot',
            'reviews' => function ($q) {
                $q->with('user:id,name');
            },
        ]);

        return Inertia::render('admin/food/orders/show', [
            'order' => $order,
        ]);
    }

    /**
     * POST /admin/food/orders/{order}/cancel — Admin cancel order with refund.
     */
    public function cancel(Request $request, FoodOrder $order)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!$order->canBeCancelled()) {
            return back()->withErrors(['order' => 'This order cannot be cancelled in its current status.']);
        }

        DB::beginTransaction();

        try {
            // Determine final status based on payment status
            if ($order->isPaid()) {
                $newStatus = FoodOrder::STATUS_REFUNDED;
            } else {
                $newStatus = FoodOrder::STATUS_CANCELLED;
            }

            $order->update([
                'status' => $newStatus,
                'cancellation_reason' => 'Admin: ' . $validated['reason'],
                'cancelled_at' => now(),
            ]);

            // Record status history with the correct final status
            DB::table('food_order_status_history')->insert([
                'food_order_id' => $order->id,
                'status' => $newStatus,
                'changed_by' => $request->user()->id,
                'metadata' => json_encode(['reason' => 'Admin: ' . $validated['reason']]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Process refund if payment was made
            if ($order->isPaid()) {
                $order->update([
                    'payment_status' => FoodOrder::PAYMENT_STATUS_REFUNDED,
                    'refund_amount' => $order->total_amount,
                    'refunded_at' => now(),
                ]);
            }

            DB::commit();

            return back()->with('success', 'Order cancelled and refund processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['order' => 'Failed to cancel order: ' . $e->getMessage()]);
        }
    }
}
