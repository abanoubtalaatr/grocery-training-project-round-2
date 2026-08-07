<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\Order\IndexOrderAction;
use App\Actions\Admin\Order\ShowOrderAction;
use App\Actions\Admin\Order\UpdateOrderStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request, IndexOrderAction $action): View
    {
        $this->authorize('viewAny', Order::class);
        $orders = $action->run($request);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order, ShowOrderAction $action): View
    {
        $this->authorize('view', $order);
        $order = $action->run($order);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, UpdateOrderStatusAction $action): RedirectResponse
    {
        $this->authorize('update', $order);

        $action->run($order, $request->validated()['status']);

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order status updated successfully.');
    }
}
