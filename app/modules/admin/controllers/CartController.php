<?php

namespace app\modules\admin\controllers;

use app\models\CartItems;
use app\models\Products;
use app\models\User;
use app\modules\admin\models\Customer;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;

/**
 * Кошики: те, що зараз лежить у кошику, але ще не замовлено.
 *  - «Клієнти» — зареєстровані (кошик прив'язаний до акаунта);
 *  - «Гості» — відвідувачі без акаунта (кошик прив'язаний до їхньої сесії в браузері).
 */
class CartController extends AbstractController
{
    public const TABS = ['customers', 'guests'];

    /** скільки днів живе гостьовий кошик без змін (cron: php app/yii cart/cleanup) */
    public const GUEST_CART_DAYS = 14;


    public function actionIndex(string $tab = 'customers')
    {
        if (!in_array($tab, self::TABS, true)) {
            $tab = 'customers';
        }

        $dataProvider = $tab === 'guests' ? $this->guestCarts() : $this->customerCarts();
        $models = $dataProvider->getModels();

        // товари в кошиках поточної сторінки — одним запитом
        $items = [];
        if ($models) {
            $condition = $tab === 'guests'
                ? ['user_id' => null, 'session_id' => array_column($models, 'session_id')]
                : ['user_id' => array_map(static fn (Customer $m) => $m->id, $models)];

            foreach (CartItems::find()->with('product')->where($condition)->orderBy(['updated_at' => SORT_DESC])->all() as $item) {
                $items[$tab === 'guests' ? $item->session_id : $item->user_id][] = $item;
            }
        }

        return $this->render('index', [
            'tab' => $tab,
            'dataProvider' => $dataProvider,
            'items' => $items,
            'counts' => [
                'customers' => (int)$this->customerQuery()->count('DISTINCT u.id'),
                'guests' => (int)$this->guestQuery()->count('DISTINCT ci.session_id'),
            ],
        ]);
    }


    private function customerQuery(): ActiveQuery
    {
        return Customer::find()
            ->alias('u')
            ->innerJoin(['ci' => CartItems::tableName()], 'ci.user_id = u.id')
            ->where(['u.role' => User::ROLE_CUSTOMER]);
    }


    private function guestQuery(): Query
    {
        return (new Query())
            ->from(['ci' => CartItems::tableName()])
            ->where(['ci.user_id' => null])
            ->andWhere(['not', ['ci.session_id' => null]]);
    }


    private function customerCarts(): ActiveDataProvider
    {
        $query = $this->customerQuery()
            ->select([
                'u.*',
                'cart_positions' => new Expression('COUNT(ci.id)'),
                'cart_qty' => new Expression('SUM(ci.qty)'),
                'cart_total' => new Expression('SUM(ci.qty * COALESCE(p.price, ci.price))'),
                'cart_updated_at' => new Expression('MAX(ci.updated_at)'),
            ])
            ->leftJoin(['p' => Products::tableName()], 'p.id = ci.product_id')
            ->groupBy('u.id')
            ->with('city');

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 30],
            'sort' => $this->sort(),
        ]);
    }


    private function guestCarts(): ActiveDataProvider
    {
        $query = $this->guestQuery()
            ->select([
                'session_id' => 'ci.session_id',
                'cart_positions' => new Expression('COUNT(ci.id)'),
                'cart_qty' => new Expression('SUM(ci.qty)'),
                'cart_total' => new Expression('SUM(ci.qty * COALESCE(p.price, ci.price))'),
                'cart_created_at' => new Expression('MIN(ci.created_at)'),
                'cart_updated_at' => new Expression('MAX(ci.updated_at)'),
            ])
            ->leftJoin(['p' => Products::tableName()], 'p.id = ci.product_id')
            ->groupBy('ci.session_id');

        return new ActiveDataProvider([
            'query' => $query,
            'key' => 'session_id',
            'pagination' => ['pageSize' => 30],
            'sort' => $this->sort(),
        ]);
    }


    private function sort(): array
    {
        return [
            'defaultOrder' => ['cart_updated_at' => SORT_DESC],
            'attributes' => [
                'cart_updated_at' => ['asc' => ['cart_updated_at' => SORT_ASC], 'desc' => ['cart_updated_at' => SORT_DESC]],
                'cart_total' => ['asc' => ['cart_total' => SORT_ASC], 'desc' => ['cart_total' => SORT_DESC]],
                'cart_qty' => ['asc' => ['cart_qty' => SORT_ASC], 'desc' => ['cart_qty' => SORT_DESC]],
            ],
        ];
    }
}
