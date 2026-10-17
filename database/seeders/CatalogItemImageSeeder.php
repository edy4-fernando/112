<?php

namespace Database\Seeders;

use App\Models\CatalogItem;
use Illuminate\Database\Seeder;
use RuntimeException;

class CatalogItemImageSeeder extends Seeder
{
    /** @var array<string, string> */
    private const IMAGES = [
        'Slim Mix' => 'Slim_Mix.png',
        'Parasin Ten' => 'Parasin.png',
        'Cardo Mariano' => 'Cardo_Mariano.png',
        'Multi Kits' => 'MultiKits.png',
        'Diab-3TS' => 'DIAB-3TS.png',
        'Multimix' => 'MultiMix.png',
        'Warm' => 'Warmi.png',
        'Power RC' => 'POWER_RC.png',
        'Noni Premium' => 'NONI_Bottle.png',
        'Aguaje' => 'Aguaje.png',
        'Floricor' => 'Floricor.png',
        'Asflu' => 'Asflu.png',
        'R-Ford' => 'R-FORT.png',
        'Biotina' => 'Biotina.png',
        'Colágeno' => 'Colágeno.png',
        'Cere-3-Al' => 'CERE_3_AL.png',
        'HI 2' => 'HI2.png',
        'COL' => 'COL.png',
        'V4ri' => 'V4R1.png',
        'Cole 55' => 'COLE_55.png',
        'Bro-15' => 'Bro-15.png',
        'Pro 5' => 'Pros.png',
        'Cartílago de Tiburón Liquido' => 'Cartilago_de_Tiburon.png',
        'Calcio Magnesio Zinc Vit D' => 'Cal. Mag. Zing VIT_D.png',
        'Carticolágeno Flex' => 'CARTICOLÁGENO FLEX .png',
        'Multi YSS' => 'MUTI YSS.png',
        'Mepausil' => 'MEPAUSIL.png',
        'Megaton' => 'MEGATON.png',
        'Clorophyll' => 'CLOROPHYLL.png',
        'Sacha Jergon' => 'SACHA JERGON.png',
        'Rx Vig Power' => 'RX_VIG_POWER.png',
        'Cartílago de Tiburón 1L' => 'Cartilago_de_Tiburon.png',
        'Calcio Magnesio Zinc + Vit. D 1L' => 'Cal. Mag. Zing VIT_D.png',
        'Gastrizan' => 'GASTRIZAN .png',
        'Aloe Vera 1L' => 'ALOE VERA .png',
        'Glucoxrol' => 'GLUCOXROL.png',
        'Noni 1L' => 'Noni.png',
        'Colágeno Flex 1L' => 'CARTICOLÁGENO FLEX .png',
        'Carti Colágen 1L' => 'CARTI COLÁGENO 350 Gr.png',
        'Clorophill 1L' => 'CLOROPHYLL.png',
        'Colon 1L' => 'COL.png',
        'Multi Yss 1L' => 'MUTI YSS.png',
        'Chanca Piedra' => 'Chanca_Piedra.png',
        'H-Ford Caps' => 'H-FORT.png',
        'Garcinia Cambogia' => 'Garcinia Canbogia.png',
        'Noni Caps' => 'Noni.png',
        'D Prost 5' => 'D-Prost_5.png',
        'Achiote' => 'Achiote.png',
        'Troya' => 'Troya.png',
        'Huanarpo Macho Caps' => 'Huanarpo_Macho.png',
        'Colágeno Hidrolizado Caps' => 'COLÁGENO HIDROLIZADO.png',
        'Vitamina C Premium Caps' => 'VITAMINA_C.png',
        'F4t Control' => 'FAT_CONTROL.png',
        'Uña de Gato Caps' => 'Uña_de_Gato.png',
        'Cúrcuma Caps' => 'CÚRCUMA PLUS.png',
        'Alcachofa Caps' => 'Alcachofa.png',
        'Bio Prost' => 'BIO PROST.png',
        'Saw Palmetto' => 'SAW PALMETO.png',
        'Fen Mix' => 'FEN_MIX.png',
        'Espirulina Caps' => 'ESPIRULINA.png',
        'Hercampuri Caps' => 'HERCAMPURI.png',
        'Colágeno Hidrolizado 6 en 1' => 'COLÁGENO HIDROLIZADO.png',
        'Cloruro de Magnesio Pote' => 'HARINA DE CAMU CAMU CON CLORURO DE MAGNESIO.png',
        'Hofem Batido' => 'HOFEM.png',
        'Colágeno Camu Camu' => 'COLÁGENO CAMU CAMU 500 Gr.png',
        'Fibrax' => 'FIBRAX.png',
        'Carti Colágeno Pote' => 'CARTI COLÁGENO 350 Gr.png',
        'Vitamina C Pote' => 'VITAMINA C 350 Gr.png',
        'Colágeno Calcio Magnesio Zinc' => 'HARINA DE COLÁGENO HIDROLIZADO CON CALCIO, MAGNESIO Y ZINC.png',
        'Colágeno Arándanos Premium' => 'COLÁGENO CON ARÁNDANOS.png',
        'Levadura de Cerveza' => 'LEVADURA DE CERVEZA.png',
        'Colon Clean Premium' => 'COLON CLEAN PREMIUM.png',
        'Vitale Premium' => 'VITALE.png',
        'Espirulina Pote' => 'ESPIRULINA PREMIUM.png',
        'Maca Negra Pote' => 'MACA NEGRA.png',
        'Cúrcuma Plus Pote' => 'CÚRCUMA PLUS.png',
        'Citrato de Magnesio Pote' => 'CITRATO DE MAGNESIO.png',
        'Hemoglobina Pote' => 'HEMOGLOBINA.png',
        'Aguaje Premium Pote' => 'Aguaje.png',
        'Maca Roja Pote' => 'MACA ROJA.png',
        'Colágeno + Biotina Pote' => 'COLÁGENO HIDROLIZADO + BIOTINA 250 Gr.png',
        'Vital Kits Pote' => 'VITAL KITS 200 Gr.png',
        'Chupa Panza' => 'CHUPA PANZA 350 Gr.png',
        '7 en 1 Pote' => '7 EN 1 350 Gr.png',
        'Nutri Punch Power' => 'NUTRI PUNCH POWER.png',
        'Huanarpo Macho Harina' => 'Huanarpo_Macho.png',
        'Maca Negra Harina' => 'MACA NEGRA.png',
        'Cúrcuma Harina' => 'Curcuma.png',
        'Espirulina Harina' => 'ESPIRULINA EN HARINA.png',
    ];

    public function run(): void
    {
        $updatedProducts = 0;

        foreach (self::IMAGES as $productName => $filename) {
            if (! is_file(public_path('images/products/'.$filename))) {
                throw new RuntimeException("No se encontró la imagen {$filename} en public/images/products.");
            }

            $updatedProducts += CatalogItem::query()
                ->where('name', $productName)
                ->update(['image' => $filename]);
        }

        $this->command?->info("Se asignaron imágenes a {$updatedProducts} productos del catálogo.");
    }
}
