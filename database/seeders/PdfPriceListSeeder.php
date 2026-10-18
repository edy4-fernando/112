<?php

namespace Database\Seeders;

use App\Models\CatalogItem;
use Illuminate\Database\Seeder;

class PdfPriceListSeeder extends Seeder
{
    /** @var array<string, array{category: string, presentation: string, rows: string}> */
    private const GROUPS = [
        'Bebidas' => [
            'category' => 'Bebidas naturales',
            'presentation' => 'Bebida Concentrada',
            'rows' => 'ASFLU|700 ml|39.90
AGUAJE|700 ml|39.90
BRO 15|700 ml|39.90
CARTILAGO DE TIBURON|700 ml|39.90
CARTICOLAGENO|700 ml|39.90
CEREBRAL|700 ml|39.90
COLAGEN|700 ml|39.90
COL|700 ml|39.90
COLESS|700 ml|39.90
DIABT3S|700 ml|39.90
FLORICOR|700 ml|39.90
HI2|700 ml|39.90
MEPAUSIL|700 ml|39.90
MEGATON|700 ml|39.90
MIX PLANTAS / MULTIMIX|700 ml|39.90
NONI|700 ml|39.90
PARASIN|700 ml|39.90
PRO 5|700 ml|39.90
POWER RC|700 ml|39.90
R-FORT|700 ml|39.90
SABILA|700 ml|39.90
WARMI|700 ml|39.90
CLOROPHYLL|700 ml|39.90
SLIN MIX|700 ml|39.90',
        ],
        'Cápsulas' => [
            'category' => 'Suplementos',
            'presentation' => 'Cápsulas',
            'rows' => 'ACHIOTEC|100 cápsulas|39.90
AGUAJE|100 cápsulas|39.90
ALGAS MARINAS|100 cápsulas|39.90
AJOS|100 cápsulas|39.90
ALCACHOFA|100 cápsulas|39.90
CANCHALAGUA|100 cápsulas|39.90
CAFÉ VERDE|100 cápsulas|39.90
CARTICOLAGENO|100 cápsulas|39.90
CARTILAGO DE TIBURON|100 cápsulas|39.90
CHANCAPIEDRA|100 cápsulas|39.90
COLAGENO HIDROLIZADO|100 cápsulas|39.90
CURCUMA|100 cápsulas|39.90
ENER UP|100 cápsulas|39.90
ESPIRULINA|100 cápsulas|39.90
FAT CONTROL|100 cápsulas|39.90
FATLIN|100 cápsulas|39.90
FEN MIX|100 cápsulas|39.90
GARCINIA CAMBOGIA|100 cápsulas|39.90
GINGO BILOBA|100 cápsulas|39.90
GLUCOSAMINA|100 cápsulas|39.90
GRAVIOLA|100 cápsulas|39.90
HERCAMPURI|100 cápsulas|39.90
H FORD|100 cápsulas|39.90
HUANARPO MACHO|100 cápsulas|39.90
HGH|100 cápsulas|39.90
LEVADURA DE CERVEZA|100 cápsulas|39.90
MACA NEGRA|100 cápsulas|39.90
MACA ROJA|100 cápsulas|39.90
MORINGA|100 cápsulas|39.90
NONI|100 cápsulas|39.90
ORTIGA|100 cápsulas|39.90
PALMITO|100 cápsulas|39.90
PROS MEN|100 cápsulas|39.90
RC MIX|100 cápsulas|39.90
SACHA INCHI|100 cápsulas|39.90
TROYA|100 cápsulas|39.90
TOCOSH|100 cápsulas|39.90
UÑA DE GATO|100 cápsulas|39.90
VITALE PREMIUM|100 cápsulas|39.90
VITAMINA C|100 cápsulas|39.90
WARM|100 cápsulas|39.90
ZEUS|100 cápsulas|39.90',
        ],
        'Harinas y polvos' => [
            'category' => 'Harinas y polvos',
            'presentation' => 'Harina en Pote',
            'rows' => 'Colágeno|1 kg|150.00
Colágeno Hidrolizado|500 g|70.00
Colágeno Hidrolizado|250 g|35.00
Estevia|120 g|30.00
Estevia|60 g|15.00
Fibrax|500 g|90.00
Hofem|500 g|60.00
Levadura de Cerveza|500 g|50.00
Maca|500 g|50.00
Cloruro de Magnesio|500 g|50.00
Cloruro de Magnesio|200 g|30.00
Vitamina C|350 g|70.00
Carticolágeno|350 g|50.00
Vitale Premium|350 g|70.00
Mass Musculature|Envase|150.00
Espirulina|350 g|70.00
7 Pepas|Envase|10.00
Fibrax|100 g|18.00
Cúrcuma|100 g|18.00
Tocosh|100 g|18.00
Graviola|100 g|18.00
Levadura de Cerveza|100 g|18.00
Espirulina|100 g|25.00
Moringa|100 g|25.00',
        ],
        'Aceites y otros' => [
            'category' => 'Otros productos naturales',
            'presentation' => 'Aceites y otros',
            'rows' => 'Aceite de Oliva|250 ml|20.00
Aceite de Oliva|500 ml|35.00
Aceite de Sacha Inchi|250 ml|35.00
Sangre de Grado Gotero|30 ml|15.00
Aceite de Copaiba Gotero|30 ml|12.00
Vinagre de Manzana|1 L|20.00
Vinagre de Manzana|1/2 L|10.00
Propóleo|120 ml|25.00
Frotación Abbe Meed|Envase|30.00
Frotación Variados|Envase|12.00
Crema Baba de Caracol|Envase|25.00',
        ],
    ];

    public function run(): void
    {
        $created = 0;
        $repriced = 0;

        foreach (self::GROUPS as $group) {
            foreach (preg_split('/\R/', $group['rows']) ?: [] as $row) {
                [$name, $size, $price] = array_map('trim', explode('|', $row));
                $product = CatalogItem::query()
                    ->where('name', $name)
                    ->where('presentation_type', $group['presentation'])
                    ->where('weight_volume', $size)
                    ->first();

                if ($product) {
                    if ((float) $product->price_public !== (float) $price) {
                        $product->update(['price_public' => $price]);
                        $repriced++;
                    }

                    continue;
                }

                CatalogItem::create([
                    'brand' => 'Carmelitas',
                    'name' => $name,
                    'health_category' => $group['category'],
                    'presentation_type' => $group['presentation'],
                    'weight_volume' => $size,
                    'price_public' => $price,
                    'stock' => 10,
                ]);
                $created++;
            }
        }

        $this->command?->info("Lista PDF aplicada: {$created} productos agregados, {$repriced} precios actualizados.");
    }
}
