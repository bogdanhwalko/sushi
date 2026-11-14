<?php

namespace app\modules\admin\controllers;

use app\models\CartItems;
use app\models\Products;
use app\models\User;
use app\modules\admin\models\Customer;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Кошики зареєстрованих клієнтів (те, що зараз лежить у кошику, але ще не замовлено).
 */
class CartController extends AbstractController
{
    public function actionIndex()
    {
        $query = Customer::find()
            ->alias('u')
            ->select([
                'u.*',
                'cart_positions' => new Expression('COUNT(ci.id)'),
                'cart_qty' => new Expression('SUM(ci.qty)'),
                'cart_total' => new Expression('SUM(ci.qty * COALESCE(p.price, ci.price))'),
                'cart_updated_at' => new Expression('MAX(ci.updated_at)'),
            ])
            ->innerJoin(['ci' => CartItems::tableName()], 'ci.user_id = u.id')
            ->leftJoin(['p' => Products::tableName()], 'p.id = ci.product_id')
            ->where(['u.role' => User::ROLE_CUSTOMER])
            ->groupBy('u.id')
            ->with('city');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 30],
            'sort' => [
                'defaultOrder' => ['cart_updated_at' => SORT_DESC],
                'attributes' => [
                    'cart_updated_at' => ['asc' => ['cart_updated_at' => SORT_ASC], 'desc' => ['cart_updated_at' => SORT_DESC]],
                    'cart_total' => ['asc' => ['cart_total' => SORT_ASC], 'desc' => ['cart_total' => SORT_DESC]],
                    'cart_qty' => ['asc' => ['cart_qty' => SORT_ASC], 'desc' => ['cart_qty' => SORT_DESC]],
                ],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
