<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\AdminOrderCreateRequest;
use App\Http\Requests\Order\AdminOrderDeleteRequest;
use App\Http\Requests\Order\AdminOrderUpdateRequest;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class OrderController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orders = Order::with(['createdForUser', 'orderProducts']);

        if ($request->filled('search')) {
            $orders->orWhereHas('createdForUser', function ($query) use ($request) {
                $query->where('email', 'like', '%' . $request->input('search') . '%');
            });

            $orders->orWhereHas('createdForUser.profile', function ($query) use ($request) {
                $query->where('first_name', 'like', '%' . $request->input('search') . '%')
                    ->orWhere('last_name', 'like', '%' . $request->input('search') . '%');
            });
        }

        $orders->whereYear('for_date', $request->input('order_year', today()->year))
            ->whereMonth('for_date', $request->input('order_month', today()->month));

        if ($request->filled('order_status')) {
            $orders->where('status', $request->input('order_status'));
        }

        $orders = $orders->orderBy('for_date', 'desc')
            ->paginate($request->input('per_page', 10));

        $years = Order::select(DB::raw('EXTRACT(year from for_date) as year'))
            ->groupBy('year')->get();

        return view('admin.orders.index', compact('orders', 'years'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  User $user
     *
     * @return \Illuminate\Http\Response
     */
    public function create(User $user)
    {
        $products = Product::active()->get();
        return view('admin.orders.create', compact('products', 'user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  AdminOrderCreateRequest $request
     * @param  User $user
     *
     * @return \Illuminate\Http\Response
     */
    public function store(AdminOrderCreateRequest $request, User $user)
    {
        DB::beginTransaction();

        $order = new Order();
        $order->fill($request->only(['staff_notes', 'customer_notes']));
        $order->createdByUser()->associate($request->user());
        $order->createdForUser()->associate($user);
        $order->for_date = today();
        $order->save();

        $products = collect($request->input('products', []))
            ->filter(function ($product) {
                return Arr::get($product, 'quantity') &&
                    Arr::get($product, 'unit_price');
            })->unique('id');

        $products->each(function ($product, $key) use ($order) {
            $orderProduct = new OrderProduct();
            $orderProduct->fill($product);
            $orderProduct->product()->associate($product['id']);
            $order->orderProducts()->save($orderProduct);
        });

        DB::commit();
        event(new OrderCreated($order));

        alert()->success('Order was created successfully.');
        return redirect()->route('admin.orders.edit', $order);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order $order
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        $order->loadMissing([
            'createdForUser', 'createdForUser.profile', 'orderProducts', 'orderProducts.product',
        ]);

        $newProducts = Product::active()->whereNotIn('id', $order->orderProducts->pluck('product_id'))->get();

        return view('admin.orders.edit', compact('order', 'newProducts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  AdminOrderUpdateRequest $request
     * @param  \App\Models\Order $order
     *
     * @return \Illuminate\Http\Response
     */
    public function update(AdminOrderUpdateRequest $request, Order $order)
    {
        DB::beginTransaction();

        $order->fill($request->only(['staff_notes', 'customer_notes']));
        $order->save();

        $products = collect($request->input('products', []))
            ->filter(function ($product) {
                return Arr::get($product, 'quantity') &&
                    Arr::get($product, 'unit_price');
            })->unique('id');

        $order->orderProducts()->delete();

        $products->each(function ($product, $key) use ($order) {
            $orderProduct = new OrderProduct();
            $orderProduct->fill($product);
            $orderProduct->product()->associate($product['id']);
            $order->orderProducts()->save($orderProduct);
        });

        DB::commit();

        alert()->success('Order was updated successfully.');
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Order $order
     *
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, Order $order)
    {
        $oldOrder = $order->replicate();
        $this->validate($request, [
            'status' => 'bail|required|string|in:' . implode(',', config('project.order_status', [])),
        ]);

        $order->status = $request->input('status');
        $order->save();

        event(new OrderStatusChanged($oldOrder, $order));
        alert()->success('Order status was updated successfully.');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AdminOrderDeleteRequest $request
     * @param Order $order
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(AdminOrderDeleteRequest $request, Order $order)
    {
        $order->delete();
        alert()->success('Order was deleted successfully.');
        return redirect()->route('admin.orders.index');
    }
}
