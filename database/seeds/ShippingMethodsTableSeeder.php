<?php

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodsTableSeeder extends Seeder
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
                'name' => 'Бесплатная доставка',
                'price' => 0
            ],
            [
                'name' => 'Средняя Доставка',
                'price' => 500
            ],
            [
                'name' => 'Быстрая доставка',
                'price' => 1000
            ]
        ])->each(function ($shipping) {
            factory(ShippingMethod::class)->create([
                'name' => $shipping['name'],
                'price' => $shipping['price']
            ]);
        });
    }
}
