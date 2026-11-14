<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\web\UserEvent;

/**
 * Позиція кошика.
 *
 * Власник кошика:
 *  - залогінений користувач — рядки з його user_id (кошик однаковий на всіх пристроях);
 *  - гість — рядки з його session_id і user_id = NULL.
 *
 * @property int $id
 * @property string|null $session_id
 * @property int|null $user_id
 * @property int $product_id
 * @property int $qty
 * @property int $price
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Products $product
 * @property User|null $user
 */
class CartItems extends ActiveRecord
{
    /** Максимальна кількість одного товару в кошику */
    public const MAX_QTY = 20;


    public static function tableName(): string
    {
        return 'cart_item';
    }


    public function rules(): array
    {
        return [
            [['product_id', 'price'], 'required'],

            [['product_id', 'user_id'], 'integer'],
            ['qty', 'default', 'value' => 1],
            ['qty', 'integer', 'min' => 1, 'max' => self::MAX_QTY],

            ['session_id', 'string', 'max' => 64],

            ['price', 'number'],
        ];
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('CURRENT_TIMESTAMP'),
            ],
        ];
    }


    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'session_id' => 'Сесія користувача',
            'user_id' => 'Клієнт',
            'product_id' => 'Товар',
            'qty' => 'Кількість',
            'price' => 'Ціна за одиницю',
            'created_at' => 'Додано',
            'updated_at' => 'Оновлено',
        ];
    }


    public function getProduct(): ActiveQuery
    {
        return $this->hasOne(Products::class, ['id' => 'product_id']);
    }


    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }


    /**
     * Вартість позиції за поточною ціною товару (так само рахує кошик і замовлення).
     */
    public function getTotal(): int
    {
        return (int)$this->qty * (int)($this->product->price ?? $this->price);
    }


    public function incrementQty(int $by = 1): void
    {
        $this->qty = min((int)$this->qty + $by, self::MAX_QTY);
    }


    public function decrementQty(int $by = 1): void
    {
        $this->qty = max((int)$this->qty - $by, 1);
    }


    /* ===================== Кошик поточного відвідувача ===================== */

    /**
     * Ідентифікатор гостьового кошика в сесії. Окремий від ID сесії PHP,
     * бо той змінюється при вході, а кошик має перейти в акаунт (див. mergeGuestCart).
     * 32 випадкові символи — збіг практично неможливий, перевіряти БД не потрібно.
     */
    public static function ensureSessionId(): string
    {
        $session = Yii::$app->session;
        $sessionId = $session->get('session_id');

        if (empty($sessionId)) {
            $sessionId = Yii::$app->security->generateRandomString(32);
            $session->set('session_id', $sessionId);
        }

        return $sessionId;
    }


    /**
     * Умова для вибірки кошика поточного відвідувача.
     * Для гостя без сесійного ідентифікатора — умова, що нічого не знаходить.
     */
    public static function ownerCondition(): array
    {
        $webUser = Yii::$app->user;
        if (!$webUser->isGuest) {
            return ['user_id' => $webUser->id];
        }

        $sessionId = Yii::$app->session->get('session_id');
        if (empty($sessionId)) {
            return ['id' => 0];
        }

        return ['session_id' => $sessionId, 'user_id' => null];
    }


    /** Позиції кошика поточного відвідувача */
    public static function findCurrent(): ActiveQuery
    {
        return static::find()->where(static::ownerCondition());
    }


    /** Нова позиція, вже прив'язана до поточного відвідувача */
    public static function newForCurrent(): static
    {
        $webUser = Yii::$app->user;

        return new static([
            'session_id' => Yii::$app->session->get('session_id'),
            'user_id' => $webUser->isGuest ? null : $webUser->id,
        ]);
    }


    public static function clearCurrent(): int
    {
        return static::deleteAll(static::ownerCondition());
    }


    /**
     * Видаляє покинуті гостьові кошики — позиції без акаунта, які не змінювались $days днів.
     * Кошики зареєстрованих клієнтів не чіпаємо.
     * @return int кількість видалених позицій
     */
    public static function deleteAbandonedGuestItems(int $days): int
    {
        return static::deleteAll(['and',
            ['user_id' => null],
            ['<', 'updated_at', new Expression('NOW() - INTERVAL :days DAY', [':days' => max(1, $days)])],
        ]);
    }


    public static function getTotalCount(): int
    {
        return (int)static::findCurrent()->sum('qty');
    }


    /**
     * Обробник події User::EVENT_AFTER_LOGIN (підключений у config/web.php):
     * товари, додані гостем до входу, переносяться в кошик акаунта.
     * Якщо товар уже є в кошику акаунта — кількості сумуються.
     */
    public static function mergeGuestCart(UserEvent $event): void
    {
        $sessionId = Yii::$app->session->get('session_id');
        $userId = $event->identity->getId();

        if (empty($sessionId) || empty($userId)) {
            return;
        }

        $guestItems = static::find()->where(['session_id' => $sessionId, 'user_id' => null])->all();
        if (empty($guestItems)) {
            return;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            /** @var static $guestItem */
            foreach ($guestItems as $guestItem) {
                $existing = static::findOne(['user_id' => $userId, 'product_id' => $guestItem->product_id]);

                if ($existing !== null) {
                    $existing->incrementQty((int)$guestItem->qty);
                    $existing->price = $guestItem->price;
                    $existing->save(false);
                    $guestItem->delete();
                } else {
                    $guestItem->user_id = $userId;
                    $guestItem->save(false);
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e, __METHOD__);
        }
    }
}
