<?php

namespace Database\Seeders;

use App\Models\DailyPosition;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if already seeded
        if (Product::count() > 0) {
            $this->command->warn('Demo data already present. Skipping.');
            return;
        }

        $admin     = User::where('role', 'admin')->first();
        $attendant = User::where('role', 'attendant')->first();

        if (! $attendant) {
            $attendant = User::create([
                'name'     => 'John Attendant',
                'username' => 'john',
                'password' => Hash::make('john123'),
                'role'     => 'attendant',
                'active'   => true,
            ]);
        }

        // ---- PRODUCTS ----
        $products = [
            ['USB Cable',        'USB001', 'Cables',             100,  150,  50, 10],
            ['USB-C Cable',      'USB-C-001', 'Phone Accessories', 120, 200, 40, 10],
            ['Wireless Mouse',   'MOU001', 'Computer Accessories', 500, 750,  15,  5],
            ['Keyboard',         'KEY001', 'Computer Accessories', 800, 1200, 8,  3],
            ['Flash Disk 32GB',  'FD032',  'Storage',            700,  1000, 12,  4],
            ['Flash Disk 64GB',  'FD064',  'Storage',            1100, 1500, 6,   3],
            ['HDMI Cable',       'HDMI01', 'Cables',             600,  900,  4,   3], // LOW
            ['Ethernet Cable',   'ETH001', 'Networking',         150,  250,  25,  8],
            ['Phone Charger',    'CHG001', 'Phone Accessories',  350,  550,  18,  6],
            ['Screen Cleaner',   'CLN001', 'Other',              80,   150,  30,  10],
            ['Laptop Bag',       'BAG001', 'Other',              1200, 1800, 5,   2],
            ['Power Bank 10K',   'PB010',  'Phone Accessories',  1400, 2000, 3,   2], // LOW
        ];

        foreach ($products as [$name, $sku, $cat, $buy, $sell, $stock, $low]) {
            $p = Product::create([
                'name'          => $name,
                'sku'           => $sku,
                'category'      => $cat,
                'buying_price'  => $buy,
                'selling_price' => $sell,
                'stock'         => $stock,
                'low_stock'     => $low,
                'active'        => true,
            ]);

            StockAdjustment::create([
                'product_id' => $p->id,
                'user_id'    => $admin->id,
                'type'       => 'purchase',
                'quantity'   => $stock,
                'reason'     => 'Opening stock (demo)',
            ]);
        }

        // ---- SERVICES ----
        $services = [
            ['Printing (B&W per page)', 10],
            ['Printing (Colour per page)', 30],
            ['Photocopy (B&W per page)', 5],
            ['Scanning (per page)', 20],
            ['Typing (per page)', 30],
            ['Lamination', 50],
            ['Binding', 100],
            ['KRA Services', 100],
            ['eCitizen Services', 100],
            ['Passport Application', 200],
            ['CV Printing', 100],
            ['Internet Use (per hour)', 100],
        ];

        foreach ($services as [$name, $price]) {
            Service::updateOrCreate(
                ['name' => $name],
                ['price' => $price, 'active' => true]
            );
        }

        // ---- EXPENSES ----
        $expenses = [
            ['Electricity tokens',     3500, 'Electricity', today()->subDays(3)],
            ['Internet subscription',  2000, 'Internet',    today()->subDays(3)],
            ['Shop rent',              15000, 'Rent',       today()->subDays(5)],
            ['Office stationery',       800, 'Stationery', today()->subDays(2)],
            ['Transport',               500, 'Transport',  today()],
        ];

        foreach ($expenses as [$desc, $amt, $cat, $date]) {
            Expense::create([
                'description' => $desc,
                'amount'      => $amt,
                'category'    => $cat,
                'user_id'     => $admin->id,
                'date'        => $date,
            ]);
        }

        // ---- SALES (across the last 10 days) ----
        $allProducts = Product::all();
        $allServices = Service::all();

        for ($day = 10; $day >= 0; $day--) {
            $date     = today()->subDays($day);
            $salesCount = rand(3, 8);

            for ($i = 0; $i < $salesCount; $i++) {
                $saleUser = rand(0, 1) ? $admin : $attendant;

                $sale = Sale::create([
                    'invoice_no'      => 'INV-' . str_pad((string) (Sale::withVoided()->max('id') + 1), 6, '0', STR_PAD_LEFT),
                    'user_id'         => $saleUser->id,
                    'total'           => 0,
                    'payment_method'  => ['cash', 'mpesa', 'mpesa', 'card'][rand(0, 3)],
                    'mpesa_reference' => null,
                    'created_at'      => $date->copy()->setTime(rand(9, 18), rand(0, 59)),
                    'updated_at'      => $date->copy()->setTime(rand(9, 18), rand(0, 59)),
                ]);

                $total = 0;

                // 1-3 products
                $picks = $allProducts->random(rand(0, 3));
                foreach ($picks as $product) {
                    $qty = rand(1, 2);
                    $sub = $product->selling_price * $qty;
                    $total += $sub;

                    SaleItem::create([
                        'sale_id'      => $sale->id,
                        'item_type'    => 'product',
                        'item_id'      => $product->id,
                        'name'         => $product->name,
                        'price'        => $product->selling_price,
                        'buying_price' => $product->buying_price,
                        'qty'          => $qty,
                        'subtotal'     => $sub,
                    ]);
                }

                // 1-2 services
                $spicks = $allServices->random(rand(0, 2));
                foreach ($spicks as $service) {
                    $qty = rand(1, 5);
                    $sub = $service->price * $qty;
                    $total += $sub;

                    SaleItem::create([
                        'sale_id'      => $sale->id,
                        'item_type'    => 'service',
                        'item_id'      => $service->id,
                        'name'         => $service->name,
                        'price'        => $service->price,
                        'buying_price' => 0,
                        'qty'          => $qty,
                        'subtotal'     => $sub,
                    ]);
                }

                $sale->update(['total' => $total]);
            }
        }

        // ---- DAILY POSITIONS ----
        for ($day = 5; $day >= 0; $day--) {
            $date = today()->subDays($day);

            foreach (['opening', 'closing'] as $type) {
                $variance = $type === 'closing' ? rand(-5000, 15000) : 0;

                DailyPosition::updateOrCreate(
                    ['date' => $date->toDateString(), 'type' => $type],
                    [
                        'kcb'         => 11000 + $variance,
                        'equity'      => 56500 + $variance,
                        'absa'        => 15000,
                        'mpesa'       => 60000 + $variance,
                        'cash'        => 20000 + $variance,
                        'other'       => 0,
                        'total'       => 162500 + $variance,
                        'user_id'     => $attendant->id,
                        'notes'       => 'Demo data',
                    ]
                );
            }
        }

        $this->command->info('Demo data seeded successfully.');
        $this->command->info('Attendant login: john / john123');
    }
}