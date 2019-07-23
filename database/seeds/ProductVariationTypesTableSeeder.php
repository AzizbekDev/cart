<?php

use Illuminate\Database\Seeder;
use App\Models\ProductVariationType;

class ProductVariationTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect([
            [
                'name' => 'Новинка',
            ],
            [
                'name' => 'Со скидкой',
            ]
        ])->each(function ($types) {
            factory(ProductVariationType::class)->create([
                'name' => $types['name'],
            ]);
        });
    }
}
