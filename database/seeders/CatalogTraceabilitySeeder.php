<?php

namespace Database\Seeders;

use App\Models\CatalogItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CatalogTraceabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today();

        CatalogItem::query()->orderBy('id')->each(function (CatalogItem $product) use ($today): void {
            $product->forceFill([
                'lot_number' => $product->lot_number ?: 'DEMO-'.$today->format('Y').'-'.str_pad((string) $product->id, 4, '0', STR_PAD_LEFT),
                'manufactured_at' => $product->manufactured_at ?? $today->copy()->subMonths(3)->subDays($product->id % 30),
                'expires_at' => $product->expires_at ?? ($product->id <= 3
                    ? $today->copy()->addDays(7 + ($product->id * 7))
                    : $today->copy()->addMonths(6 + ($product->id % 12))),
                'shelf_location' => $product->shelf_location ?: 'DEMO Estante '.chr(65 + (($product->id - 1) % 5)).'-'.str_pad((string) (intdiv($product->id - 1, 5) % 20 + 1), 2, '0', STR_PAD_LEFT).', nivel '.(($product->id - 1) % 3 + 1),
            ])->save();
        });
    }
}
