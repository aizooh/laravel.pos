<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PosController extends Controller
{
    public function index()
    {
        $products = Product::active()->orderBy('name')->get();
        $services = Service::active()->orderBy('name')->get();
        $cart     = $this->cart();

        return view('pos.index', compact('products', 'services', 'cart'));
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $productQuery = Product::active();
        $serviceQuery = Service::active();

        if ($q !== '') {
            $productQuery->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%");
            });
            $serviceQuery->where('name', 'like', "%{$q}%");
        }

        $products = $productQuery->orderBy('name')->limit(100)->get();
        $services = $serviceQuery->orderBy('name')->limit(100)->get();

        return response()->json([
            'products' => $products->map(fn ($p) => [
                'id'    => $p->id,
                'name'  => $p->name,
                'sku'   => $p->sku,
                'price' => (float) $p->selling_price,
                'stock' => $p->stock,
            ]),
            'services' => $services->map(fn ($s) => [
                'id'    => $s->id,
                'name'  => $s->name,
                'price' => (float) $s->price,
            ]),
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:product,service',
            'id'   => 'required|integer',
            'qty'  => 'nullable|integer|min:1',
        ]);

        $qty = $data['qty'] ?? 1;

       $buying = 0;
if ($data['type'] === 'product') {
    $item   = Product::active()->findOrFail($data['id']);
    $name   = $item->name;
    $price  = (float) $item->selling_price;
    $buying = (float) $item->buying_price;
    $stock  = $item->stock;
} else {
    $item  = Service::active()->findOrFail($data['id']);
    $name  = $item->name;
    $price = (float) $item->price;
    $stock = null;
}

        $key  = $data['type'] . ':' . $item->id;
        $cart = $this->cart();

        $newQty = isset($cart[$key]) ? $cart[$key]['qty'] + $qty : $qty;

        if ($data['type'] === 'product' && $newQty > $stock) {
            return response()->json(['error' => "Only {$stock} in stock."], 422);
        }

        if (isset($cart[$key])) {
            $cart[$key]['qty'] = $newQty;
        } else {
            $cart[$key] = [
                'type'  => $data['type'],
                'id'    => $item->id,
                'name'  => $name,
                'price' => $price,
                'buying_price' => $buying,
                'qty'   => $newQty,
            ];
        }

        $this->saveCart($cart);

        return $this->cartResponse();
    }

    public function updateQty(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string',
            'qty' => 'required|integer|min:1',
        ]);

        $cart = $this->cart();
        if (! isset($cart[$data['key']])) {
            return response()->json(['error' => 'Item not in cart.'], 404);
        }

        if ($cart[$data['key']]['type'] === 'product') {
            $product = Product::find($cart[$data['key']]['id']);
            if ($product && $data['qty'] > $product->stock) {
                return response()->json(['error' => "Only {$product->stock} in stock."], 422);
            }
        }

        $cart[$data['key']]['qty'] = $data['qty'];
        $this->saveCart($cart);

        return $this->cartResponse();
    }

    public function remove(Request $request)
    {
        $data = $request->validate(['key' => 'required|string']);
        $cart = $this->cart();
        unset($cart[$data['key']]);
        $this->saveCart($cart);

        return $this->cartResponse();
    }

    public function clear()
    {
        session()->forget('cart');

        return $this->cartResponse();
    }

    public function checkout(Request $request)
{
    $data = $request->validate([
        'payment_method'  => 'required|in:cash,mpesa,card',
        'mpesa_reference' => 'nullable|string|max:50',
    ]);

    $cart = $this->cart();
    if (empty($cart)) {
        return back()->withErrors(['cart' => 'Cart is empty.']);
    }

    // Pre-validate stock before touching the DB
    foreach ($cart as $item) {
        if ($item['type'] === 'product') {
            $product = Product::find($item['id']);
            if (! $product || $product->stock < $item['qty']) {
                $available = $product->stock ?? 0;
                return back()->withErrors([
                    'cart' => "Not enough stock for {$item['name']} (only {$available} left).",
                ]);
            }
        }
    }

    try {
        $sale = DB::transaction(function () use ($cart, $data, $request) {
           $nextId    = (\App\Models\Sale::withVoided()->max('id') ?? 0) + 1;
$invoiceNo = 'INV-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['qty'];
            }

            $sale = Sale::create([
                'invoice_no'      => $invoiceNo,
                'user_id'         => $request->user()->id,
                'total'           => $total,
                'payment_method'  => $data['payment_method'],
                'mpesa_reference' => $data['mpesa_reference'] ?? null,
            ]);

            foreach ($cart as $item) {
                SaleItem::create([
    'sale_id'      => $sale->id,
    'item_type'    => $item['type'],
    'item_id'      => $item['id'],
    'name'         => $item['name'],
    'price'        => $item['price'],
    'buying_price' => $item['buying_price'] ?? 0,
    'qty'          => $item['qty'],
    'subtotal'     => $item['price'] * $item['qty'],

                ]);

                if ($item['type'] === 'product') {
                    $product = Product::find($item['id']);
                    if ($product) {
                        $product->decrement('stock', $item['qty']);

                        StockAdjustment::create([
                            'product_id' => $product->id,
                            'user_id'    => $request->user()->id,
                            'type'       => 'sale',
                            'quantity'   => -$item['qty'],
                            'reason'     => "Sale {$invoiceNo}",
                        ]);
                    }
                }
            }

            return $sale;
        });
    } catch (\Throwable $e) {
        return back()->withErrors(['cart' => 'Checkout failed: ' . $e->getMessage()]);
    }

    session()->forget('cart');

    return redirect()->route('sales.receipt', $sale)
        ->with('ok', "Sale {$sale->invoice_no} completed.");
}

    private function cart(): array
    {
        return session('cart', []);
    }

    private function saveCart(array $cart): void
    {
        session(['cart' => $cart]);
    }

    private function cartResponse()
    {
        $cart  = $this->cart();
        $total = collect($cart)->sum(fn ($i) => $i['price'] * $i['qty']);
        $count = collect($cart)->sum('qty');

        return response()->json([
            'cart'  => $cart,
            'total' => round($total, 2),
            'count' => $count,
        ]);
    }
}