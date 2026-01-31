<?php

namespace app\controllers;

use app\models\Category;
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
        $categories = Category::find()
            ->select(['slug', 'name'])
            ->where(['status' => 1])
            ->asArray()
            ->all();

        $products = [
            [
                'id' => 'philadelphia',
                'name' => 'Філадельфія з тунцем Maxi(вдвічі більше риби)',
                'meta' => '8 pcs - salmon - cream cheese',
                'description' => 'Склад: рис, норі, сир філадельфія, огірок, авокадо, тунець, унагі.',
                'price' => 309,
                'price_text' => '309 UAH',
                'weight' => 'Вага: 350 г',
                'pieces' => '8 pcs',
                'category' => 'fila',
                'cities' => ['kyiv', 'lviv'],
                'city_label' => 'Kyiv / Lviv',
                'image' => 'https://osama.com.ua/_next/image?url=https%3A%2F%2Fe-admin.com.ua%2Fphoto%2Fphoto%2Fuploads%2Fosama-sushi%2F1732182906474.jpeg&w=1200&q=75',
            ],
            [
                'id' => 'philadelphia2',
                'name' => 'Філадельфія з вугрем MAXi (вдвічі більше риби)',
                'meta' => '8 pcs - salmon - cream cheese',
                'description' => 'Склад: рис, норі, сир філадельфія, огірок, авокадо, вугор, унагі, кунжут білий, кунжут чорний',
                'price' => 366,
                'price_text' => '366 UAH',
                'weight' => 'Вага: 355 г',
                'pieces' => '8 pcs',
                'category' => 'fila',
                'cities' => ['kyiv', 'lviv'],
                'city_label' => 'Kyiv / Lviv',
                'image' => 'https://e-admin.com.ua/photo/photo/uploads/osama-sushi/1732182936113.jpeg',
            ],
            [
                'id' => 'dragon',
                'name' => 'Червоний дракон',
                'meta' => '8 pcs - eel - avocado',
                'description' => 'Склад: норі, рис, огірок, тигрова креветка, сир філа, авокадо, тунець, унагі соус, ікра тобіко',
                'price' => 184,
                'price_text' => '184 UAH',
                'weight' => 'Вага: 270 г ',
                'pieces' => '8 pcs',
                'category' => 'dragon',
                'cities' => ['kyiv', 'odesa'],
                'city_label' => 'Kyiv / Odesa',
                'image' => 'https://osama.com.ua/_next/image?url=https%3A%2F%2Fe-admin.com.ua%2Fphoto%2Fphoto%2Fuploads%2Fosama-sushi%2F1732217600773.jpeg&w=1920&q=75',
            ],
            [
                'id' => 'dragon_green',
                'name' => 'Дракон зелений',
                'meta' => '32 pcs - salmon - tuna',
                'description' => 'Склад: норі, рис, сир філа, вугор, ікра тобіко, авокадо, унагі соус, кунжут, огірок',
                'price' => 177,
                'price_text' => '177 UAH',
                'weight' => 'Вага: 275 г',
                'pieces' => '32 pcs',
                'category' => 'dragon',
                'cities' => ['kyiv'],
                'city_label' => 'Kyiv',
                'image' => 'https://osama.com.ua/_next/image?url=https%3A%2F%2Fe-admin.com.ua%2Fphoto%2Fphoto%2Fuploads%2Fosama-sushi%2F1732217554089.jpeg&w=1920&q=75',
            ],
            [
                'id' => 'maki',
                'name' => 'Макі з копченим лососем',
                'meta' => 'test - test2 - test3',
                'description' => 'Склад: норі, рис, х/к',
                'price' => 74,
                'price_text' => '74 UAH',
                'weight' => 'Вага: 120 г',
                'pieces' => '10 test',
                'category' => 'maki',
                'cities' => ['kyiv'],
                'city_label' => 'kyiv',
                'image' => 'https://e-admin.com.ua/photo/photo/uploads/osama-sushi/1732218076284.jpeg',
            ],
            [
                'id' => 'maki2',
                'name' => 'Макі Філа',
                'meta' => 'test - test2 - test3',
                'description' => 'Склад: норі, рис, сир філа',
                'price' => 54,
                'price_text' => '54 UAH',
                'weight' => 'Вага: 115 г',
                'pieces' => '10 test',
                'category' => 'maki',
                'cities' => ['kyiv'],
                'city_label' => 'kyiv',
                'image' => 'https://e-admin.com.ua/photo/photo/uploads/osama-sushi/1732217998259.jpeg',
            ],
        ];

        $cityMap = [
            'all' => ['label' => 'Всі міста', 'address' => 'Всі міста'],
            'kyiv' => ['label' => 'Київ', 'address' => 'Київ, Київська 10'],
            'kyiv20' => ['label' => 'Київ', 'address' => 'Київ, Київська 20'],
            'kyiv30' => ['label' => 'Київ', 'address' => 'Київ, Київська 30'],
        ];

        $this->view->params['categories'] = $categories;
        $this->view->params['products'] = $products;
        $this->view->params['cityMap'] = $cityMap;
        $this->view->params['defaultCity'] = 'kyiv';

        return $this->render('index');
    }
}
