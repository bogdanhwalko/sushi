<?php

namespace app\modules\admin\models\search;

use app\models\Orders;
use app\models\User;
use app\modules\admin\models\Customer;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Пошук клієнтів: загальний рядок пошуку + фільтри за містом і статусом.
 */
class CustomerSearch extends Model
{
    /** Логін, ПІБ, телефон або email */
    public $q;

    public $city_id;

    public $status;


    public function rules(): array
    {
        return [
            ['q', 'trim'],
            ['q', 'string', 'max' => 100],
            [['city_id', 'status'], 'integer'],
        ];
    }


    public function formName(): string
    {
        return '';
    }


    public function search(array $params): ActiveDataProvider
    {
        $query = Customer::find()
            ->alias('u')
            ->select([
                'u.*',
                'orders_count' => new Expression('COUNT(o.id)'),
                'orders_total' => new Expression('COALESCE(SUM(o.total), 0)'),
            ])
            ->leftJoin(['o' => Orders::tableName()], 'o.user_id = u.id')
            ->where(['u.role' => User::ROLE_CUSTOMER])
            ->groupBy('u.id')
            ->with('city');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 30],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id' => ['asc' => ['u.id' => SORT_ASC], 'desc' => ['u.id' => SORT_DESC]],
                    'username' => ['asc' => ['u.username' => SORT_ASC], 'desc' => ['u.username' => SORT_DESC]],
                    'fullName' => [
                        'asc' => ['u.last_name' => SORT_ASC, 'u.first_name' => SORT_ASC],
                        'desc' => ['u.last_name' => SORT_DESC, 'u.first_name' => SORT_DESC],
                        'label' => 'ПІБ',
                    ],
                    'created_at' => ['asc' => ['u.created_at' => SORT_ASC], 'desc' => ['u.created_at' => SORT_DESC]],
                    'orders_count' => ['asc' => ['orders_count' => SORT_ASC], 'desc' => ['orders_count' => SORT_DESC]],
                    'orders_total' => ['asc' => ['orders_total' => SORT_ASC], 'desc' => ['orders_total' => SORT_DESC]],
                ],
            ],
        ]);

        if (!$this->load($params) || !$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['u.city_id' => $this->city_id])
            ->andFilterWhere(['u.status' => $this->status]);

        if ($this->q !== null && $this->q !== '') {
            $condition = ['or',
                ['like', 'u.username', $this->q],
                ['like', 'u.email', $this->q],
                ['like', new Expression("CONCAT_WS(' ', u.last_name, u.first_name, u.middle_name)"), $this->q],
            ];

            // телефон шукаємо за цифрами: "097 123" знайде 380971234567
            $digits = preg_replace('/\D+/', '', $this->q);
            if (strlen($digits) >= 3) {
                $condition[] = ['like', 'u.phone', ltrim($digits, '0')];
            }

            $query->andWhere($condition);
        }

        return $dataProvider;
    }
}
