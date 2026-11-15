<?php

namespace app\modules\admin\models\search;

use app\models\Orders;
use app\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Клієнти без реєстрації: замовлення, оформлені без входу в акаунт, згруповані за номером телефону.
 * Окремих записів для гостей немає — «гість» = телефон, з якого замовляли.
 */
class GuestCustomerSearch extends Model
{
    /** Ім'я або телефон */
    public $q;

    public $city_id;


    public function rules(): array
    {
        return [
            ['q', 'trim'],
            ['q', 'string', 'max' => 100],
            ['city_id', 'integer'],
        ];
    }


    public function formName(): string
    {
        return '';
    }


    /** Замовлення гостей (без акаунта) */
    public static function guestOrders(): Query
    {
        return (new Query())->from(['o' => Orders::tableName()])->where(['o.user_id' => null]);
    }


    /** Скільки різних гостей (телефонів) замовляли без входу */
    public static function countAll(): int
    {
        return (int)self::guestOrders()->select(new Expression('COUNT(DISTINCT o.phone)'))->scalar();
    }


    public function search(array $params): ActiveDataProvider
    {
        // останнє значення поля в групі (за найновішим замовленням)
        $latest = static fn (string $column) => new Expression(
            "SUBSTRING_INDEX(GROUP_CONCAT(o.$column ORDER BY o.id DESC SEPARATOR '\\n'), '\\n', 1)"
        );

        $accounts = (new Query())
            ->select(new Expression('COUNT(*)'))
            ->from(['u' => User::tableName()])
            ->where('u.phone = o.phone')
            ->andWhere(['u.role' => User::ROLE_CUSTOMER]);

        $query = self::guestOrders()
            ->select([
                'phone' => 'o.phone',
                'customer_name' => $latest('customer_name'),
                'city_name' => $latest('city_name'),
                'orders_count' => new Expression('COUNT(*)'),
                'orders_total' => new Expression('COALESCE(SUM(o.total), 0)'),
                'first_order_at' => new Expression('MIN(o.created_at)'),
                'last_order_at' => new Expression('MAX(o.created_at)'),
                'accounts' => $accounts,
            ])
            ->groupBy('o.phone');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'phone',
            'pagination' => ['pageSize' => 30],
            'sort' => [
                'defaultOrder' => ['last_order_at' => SORT_DESC],
                'attributes' => [
                    'customer_name',
                    'phone' => ['asc' => ['o.phone' => SORT_ASC], 'desc' => ['o.phone' => SORT_DESC]],
                    'orders_count',
                    'orders_total',
                    'first_order_at',
                    'last_order_at',
                ],
            ],
        ]);

        if (!$this->load($params) || !$this->validate()) {
            return $dataProvider;
        }

        // фільтри відбирають телефони, але агрегати рахуються по всіх замовленнях гостя
        if ($this->city_id) {
            $query->andWhere(['o.phone' => self::guestOrders()->select('o.phone')->andWhere(['o.city_id' => (int)$this->city_id])]);
        }

        if ($this->q !== null && $this->q !== '') {
            $condition = ['or', ['like', 'o.customer_name', $this->q]];

            // телефон шукаємо за цифрами: "097 123" знайде 380971234567
            $digits = preg_replace('/\D+/', '', $this->q);
            if (strlen($digits) >= 3) {
                $condition[] = ['like', 'o.phone', ltrim($digits, '0')];
            }

            $query->andWhere(['o.phone' => self::guestOrders()->select('o.phone')->andWhere($condition)]);
        }

        return $dataProvider;
    }
}
