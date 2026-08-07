<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderNoteRequest;
use App\Models\Order;
use App\Models\OrderNote;
use Illuminate\Http\RedirectResponse;

class OrderManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function updateStatus(\Illuminate\Http\Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => 'required|in:awaiting_payment,placed,processing,shipping,out_for_delivery,delivered,cancelled',
        ]);

        $previousStatus = $order->status;
        $newStatus = $validated['status'];
        $order->update(['status' => $newStatus]);

        $timestampField = match($newStatus) {
            'placed' => 'placed_at',
            'processing' => 'processing_at',
            'shipping' => 'shipping_at',
            'out_for_delivery' => 'out_for_delivery_at',
            'delivered' => 'delivered_at',
            'cancelled' => 'cancelled_at',
            default => null,
        };

        if ($timestampField) {
            $order->update([$timestampField => now()]);
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', "Order status updated from {$previousStatus} to {$newStatus}.");
    }

    public function addNote(StoreOrderNoteRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        OrderNote::create([
            'order_id' => $order->id,
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order note added successfully.');
    }

    public function deleteNote(Order $order, OrderNote $note): RedirectResponse
    {
        $this->authorize('update', $order);

        if ($note->order_id !== $order->id) {
            abort(403);
        }

        $note->delete();

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order note deleted successfully.');
    }
}
