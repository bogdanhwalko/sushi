<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $categories = [
            ['slug' => 'all', 'name' => 'All menu'],
            ['slug' => 'rolls', 'name' => 'Rolls'],
            ['slug' => 'sets', 'name' => 'Sets'],
            ['slug' => 'veggie', 'name' => 'Veggie'],
            ['slug' => 'test', 'name' => 'Test'],
            ['slug' => 'test2', 'name' => 'Test2'],
            ['slug' => 'test3', 'name' => 'Test3'],
            ['slug' => 'test4', 'name' => 'Test4'],
//            ['slug' => 'test5', 'name' => 'Test5'],
//            ['slug' => 'test6', 'name' => 'Test6'],
//            ['slug' => 'test7', 'name' => 'Test7'],
        ];

        $products = [
            [
                'id' => 'philadelphia',
                'name' => 'Philadelphia',
                'meta' => '8 pcs - salmon - cream cheese',
                'description' => 'Salmon, cream cheese, and rice.',
                'price_value' => 389,
                'price_text' => '389 UAH',
                'weight' => '260 g',
                'pieces' => '8 pcs',
                'category' => 'rolls',
                'cities' => ['kyiv', 'lviv'],
                'city_label' => 'Kyiv / Lviv',
                'image' => 'assets/sushi-philadelphia.svg',
            ],
            [
                'id' => 'dragon',
                'name' => 'Dragon',
                'meta' => '8 pcs - eel - avocado',
                'description' => 'Eel, avocado, and unagi sauce.',
                'price_value' => 429,
                'price_text' => '429 UAH',
                'weight' => '280 g',
                'pieces' => '8 pcs',
                'category' => 'rolls',
                'cities' => ['kyiv', 'odesa'],
                'city_label' => 'Kyiv / Odesa',
                'image' => 'assets/sushi-dragon.svg',
            ],
            [
                'id' => 'haru',
                'name' => 'Haru Set',
                'meta' => '32 pcs - salmon - tuna',
                'description' => 'Set for a group.',
                'price_value' => 749,
                'price_text' => '749 UAH',
                'weight' => '980 g',
                'pieces' => '32 pcs',
                'category' => 'sets',
                'cities' => ['kyiv'],
                'city_label' => 'Kyiv',
                'image' => 'assets/sushi-haru.svg',
            ],
            [
                'id' => 'test2',
                'name' => 'Test set',
                'meta' => 'test - test2 - test3',
                'description' => 'test description.',
                'price_value' => 200,
                'price_text' => '200 UAH',
                'weight' => '980 g',
                'pieces' => '10 test',
                'category' => 'test',
                'cities' => ['kyiv'],
                'city_label' => 'kyiv',
                'image' => 'assets/sushi-haru.svg',
            ],
        ];

        $cityMap = [
            'all' => ['label' => '??? ?????', 'address' => '????, ?????, ?????'],
            'kyiv' => ['label' => '????', 'address' => '????, ???. ??????????, 90'],
            'lviv' => ['label' => '?????', 'address' => '?????, ???. ??????????????, 65'],
            'odesa' => ['label' => '?????', 'address' => '?????, ??????????? ???????, 15'],
        ];

        $this->view->params['categories'] = $categories;
        $this->view->params['products'] = $products;
        $this->view->params['cityMap'] = $cityMap;
        $this->view->params['defaultCity'] = 'kyiv';

        return $this->render('index');
    }
}
