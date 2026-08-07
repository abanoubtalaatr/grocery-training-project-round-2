<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Actions\Admin\OrderIndexAction;
use App\Actions\Admin\OrderShowAction;
use App\Actions\Admin\OrderDeleteAction;
use App\Http\Requests\Admin\StoreOrderNoteRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function __construct(
        protected OrderIndexAction $orderIndexAction,
        protected OrderShowAction $orderShowAction,
        protected OrderDeleteAction $orderDeleteAction
    ) {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): View
    {
        $data = $this->orderIndexAction->execute($request);

        return view('admin.orders.index', $data);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): View
    {
        $order = $this->orderShowAction->execute($order);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Store not used for admin orders (placeholder).
     */
    public function store(Request $request): RedirectResponse
    {
        abort(405);
    }

    /**
     * Update order (general update).
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        abort(405);
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order): RedirectResponse
    {
        $this->orderDeleteAction->execute($order);

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order deleted successfully.');
    }
}
