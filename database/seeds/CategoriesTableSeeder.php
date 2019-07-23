<?php

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect([
            [ // id = 1
                'name' => 'Телевизоры, фото-видео и аудио',
                'slug' => "Телевизоры фото-видео и аудио",
                'order' => '1',
                'parent_id' => null
            ],
            [ // id = 2
                'name' => 'Ноутбуки, принтеры, компьютеры',
                'slug' => "Ноутбуки принтеры компьютеры",
                'order' => '2',
                'parent_id' => null
            ],
            [ // id = 3
                'name' => "Телефоны, гаджеты, аксессуары",
                'slug' => "Телефоны гаджеты аксессуары",
                'order' => '3',
                'parent_id' => null
            ],
            [ // id = 4
                'name' => 'Бытовая техника',
                'slug' => "Бытовая техника",
                'order' => '4',
                'parent_id' => null
            ],
            // caregory = 1
            [
                'name' => 'Телевизоры',
                'slug' => "Телевизоры",
                'order' => '1',
                'parent_id' => '1'
            ],
            [
                'name' => 'Аудио',
                'slug' => "Аудио",
                'order' => '2',
                'parent_id' => '1'
            ],
            [
                'name' => "Видеотехника",
                'slug' => "Видеотехника",
                'order' => '3',
                'parent_id' => '1'
            ],
            [
                'name' => "Фото, видео",
                'slug' => "Фото видео",
                'order' => '4',
                'parent_id' => '1'
            ],
            [
                'name' => "Игровые приставки",
                'slug' => "Игровые приставки",
                'order' => '5',
                'parent_id' => '1'
            ],
            // caregory = 2
            [
                'name' => 'Ноутбуки',
                'slug' => "Ноутбуки",
                'order' => '1',
                'parent_id' => '2'
            ],
            [
                'name' => 'Мониторы',
                'slug' => "Мониторы",
                'order' => '2',
                'parent_id' => '2'
            ],
            [
                'name' => 'Компьютерные компоненты',
                'slug' => "Компьютерные компоненты",
                'order' => '3',
                'parent_id' => '2'
            ],
            [
                'name' => 'Принтеры и сканеры',
                'slug' => "Принтеры и сканеры",
                'order' => '4',
                'parent_id' => '2'
            ],
            // caregory = 3
            [
                'name' => 'Смартфоны',
                'slug' => "Смартфоны",
                'order' => '1',
                'parent_id' => '3'
            ],
            [
                'name' => 'Аксессуары',
                'slug' => "Аксессуары",
                'order' => '2',
                'parent_id' => '3'
            ],
            [
                'name' => 'Планшеты',
                'slug' => "Планшеты",
                'order' => '3',
                'parent_id' => '3'
            ],
            [
                'name' => 'Домашние телефоны',
                'slug' => "Домашние телефоны",
                'order' => '4',
                'parent_id' => '3'
            ],
            [
                'name' => "Smart Watches",
                'slug' => "Smart Watches",
                'order' => '5',
                'parent_id' => '3'
            ],
            // caregory = 4
            [
                'name' => 'Стиральные машины',
                'slug' => "Стиральные машины",
                'order' => '1',
                'parent_id' => '4'
            ],
            [
                'name' => 'Пылесосы и аксессуары',
                'slug' => "Пылесосы и аксессуары",
                'order' => '2',
                'parent_id' => '4'
            ],
            [
                'name' => 'Климатическая техника',
                'slug' => "Климатическая техника",
                'order' => '3',
                'parent_id' => '4'
            ],
            [
                'name' => 'Техника для ухода за одеждой',
                'slug' => "Техника для ухода за одеждой",
                'order' => '4',
                'parent_id' => '4'
            ],
            [
                'name' => "Интерьерное освещение",
                'slug' => "Интерьерное освещение",
                'order' => '5',
                'parent_id' => '4'
            ],
            [
                'name' => "Швейное оборудование",
                'slug' => "Швейное оборудование",
                'order' => '6',
                'parent_id' => '4'
            ]
        ])->each(function ($cat) {
            $category = factory(Category::class)->create([
                'name' => $cat['name'],
                'slug' => str_slug($cat['slug']),
                'order' => $cat['order'],
                'parent_id' => $cat['parent_id']
            ]);
            if ($category->parent_id != null)
                $category->products()->sync(
                    factory(Product::class, 5)->create()
                );
        });
    }
}
