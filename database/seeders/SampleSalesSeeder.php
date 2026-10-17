<?php

namespace Database\Seeders;

use App\Models\CatalogItem;
use App\Models\Customer;
use App\Models\PatientOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SampleSalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = CatalogItem::query()
            ->where('stock', '>=', 10)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', Carbon::today()))
            ->orderBy('id')
            ->take(10)
            ->get();

        if ($products->count() < 4) {
            throw new RuntimeException('Se necesitan al menos cuatro productos disponibles para cargar las ventas de muestra.');
        }

        $today = Carbon::today();
        $profiles = [
            ['name' => 'Ana Quispe', 'city' => 'Cusco', 'interest' => 'Bienestar digestivo', 'age' => 34, 'visits' => 4, 'elapsed' => 42, 'remaining' => 6],
            ['name' => 'Luis Huamán', 'city' => 'Wanchaq', 'interest' => 'Nutrición y energía', 'age' => 46, 'visits' => 3, 'elapsed' => 35, 'remaining' => 18],
            ['name' => 'Rosa Ccopa', 'city' => 'San Sebastián', 'interest' => 'Bienestar general', 'age' => 52, 'visits' => 6, 'elapsed' => 52, 'remaining' => 32],
            ['name' => 'Carlos Paredes', 'city' => 'San Jerónimo', 'interest' => 'Cuidado personal', 'age' => 39, 'visits' => 5, 'elapsed' => 24, 'remaining' => 45],
            ['name' => 'Elena Huillca', 'city' => 'Cusco', 'interest' => 'Nutrición diaria', 'age' => 29, 'visits' => 7, 'elapsed' => 61, 'remaining' => 70],
        ];

        $customers = collect($profiles)->map(function (array $profile, int $index) use ($today): Customer {
            return Customer::updateOrCreate(
                ['phone' => sprintf('00000000%02d', $index + 1)],
                [
                    'name' => $profile['name'],
                    'is_demo' => false,
                    'age' => $profile['age'],
                    'city' => $profile['city'],
                    'health_interest' => $profile['interest'],
                    'last_purchase_date' => $today->copy()->subDays(($index + 1) * 2),
                    'visit_count' => $profile['visits'],
                    'segment' => 'Regular',
                    'treatment_status' => 'En Curso',
                    'treatment_start_date' => $today->copy()->subDays($profile['elapsed']),
                    'treatment_end_date' => $today->copy()->addDays($profile['remaining']),
                    'health_notes' => null,
                ],
            );
        });

        $salesCreated = 0;

        foreach (range(0, 14) as $saleNumber) {
            $month = 6 + intdiv($saleNumber, 3);
            $day = [4, 9, 14][$saleNumber % 3];
            $date = Carbon::create(2026, $month, $day, 12, 0, 0);
            $customer = $customers[$saleNumber % $customers->count()];
            $firstProduct = $products[$saleNumber % $products->count()];
            $secondProduct = $products[($saleNumber + 3) % $products->count()];
            $items = collect([$firstProduct, $secondProduct])->unique('id')->values();

            $created = DB::transaction(function () use ($customer, $date, $items): bool {
                $order = PatientOrder::firstOrCreate(
                    [
                        'customer_id' => $customer->id,
                        'channel' => 'Tienda Física',
                        'created_at' => $date,
                    ],
                    [
                        'total_amount' => 0,
                        'payment_method' => ['Efectivo', 'Yape / Plin', 'Tarjeta (POS)'][$customer->id % 3],
                        'status' => 'Completado',
                    ],
                );

                if (! $order->wasRecentlyCreated) {
                    return false;
                }

                $total = 0.0;

                foreach ($items as $item) {
                    $product = CatalogItem::query()->whereKey($item->id)->lockForUpdate()->firstOrFail();

                    if ($product->stock < 1 || $product->expires_at?->isBefore(Carbon::today())) {
                        throw new RuntimeException("No hay stock vigente para {$product->name}.");
                    }

                    $price = (float) $product->price_public;
                    $order->items()->create([
                        'catalog_item_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => 1,
                        'unit_price' => $price,
                    ]);
                    $product->decrement('stock');
                    $total += $price;
                }

                $order->update(['total_amount' => $total]);
                DB::table('patient_orders')->where('id', $order->id)->update([
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                return true;
            });

            $salesCreated += (int) $created;
        }

        $this->command?->info("Se registraron {$salesCreated} ventas de muestra; las ventas existentes no se duplicaron ni se modificaron.");
    }
}
