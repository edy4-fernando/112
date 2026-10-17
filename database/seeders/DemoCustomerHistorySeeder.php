<?php

namespace Database\Seeders;

use App\Models\CatalogItem;
use App\Models\Customer;
use App\Models\PatientOrder;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoCustomerHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = CatalogItem::query()
            ->whereIn('name', ['Garcinia Cambogia', 'H-Ford Caps'])
            ->get()
            ->keyBy('name');

        if ($products->count() !== 2) {
            throw new RuntimeException('Faltan productos para crear el historial de demostración.');
        }

        $createdCustomers = 0;

        foreach (range(1, 5) as $number) {
            $latestPurchase = Carbon::today()->subDays(3 * $number);
            $olderPurchase = Carbon::today()->subDays(35 + (3 * $number));
            $phone = sprintf('DEMO-CLIENTE-%03d', $number);

            DB::transaction(function () use ($number, $latestPurchase, $olderPurchase, $phone, $products, &$createdCustomers): void {
                $customer = Customer::updateOrCreate(
                    ['phone' => $phone],
                    [
                        'name' => sprintf('Cliente Demo %02d', $number),
                        'is_demo' => true,
                        'city' => 'Cusco',
                        'health_interest' => 'Bienestar general',
                        'last_purchase_date' => $latestPurchase,
                        'visit_count' => 2,
                        'segment' => 'Regular',
                        'treatment_status' => 'Sin Tratamiento',
                        'health_notes' => 'Registro ficticio de demostración; no corresponde a un cliente real.',
                    ],
                );

                $createdCustomers += (int) $customer->wasRecentlyCreated;

                foreach ([
                    ['date' => $olderPurchase, 'repeat' => false],
                    ['date' => $latestPurchase, 'repeat' => true],
                ] as $purchase) {
                    $order = PatientOrder::firstOrCreate(
                        [
                            'customer_id' => $customer->id,
                            'channel' => 'WhatsApp / Web',
                            'created_at' => $purchase['date']->copy()->startOfDay(),
                        ],
                        [
                            'total_amount' => $purchase['repeat'] ? 30 : 60,
                            'payment_method' => 'Yape / Plin',
                            'status' => 'Completado',
                        ],
                    );

                    $orderProducts = $purchase['repeat']
                        ? [$products[$number % 2 === 0 ? 'Garcinia Cambogia' : 'H-Ford Caps']]
                        : [$products['Garcinia Cambogia'], $products['H-Ford Caps']];

                    foreach ($orderProducts as $product) {
                        $order->items()->firstOrCreate(
                            ['catalog_item_id' => $product->id],
                            [
                                'product_name' => $product->name,
                                'quantity' => 1,
                                'unit_price' => $product->price_public,
                            ],
                        );
                    }

                    DB::table('patient_orders')->where('id', $order->id)->update([
                        'created_at' => $purchase['date']->copy()->startOfDay(),
                        'updated_at' => $purchase['date']->copy()->startOfDay(),
                    ]);
                }
            });
        }

        $this->command?->info("Se prepararon 5 clientes ficticios con historial; {$createdCustomers} se crearon nuevos.");
    }
}
