<?php

namespace app\models;

/**
 * This is the model class for table "config".
 *
 * @property int $id
 * @property string $name
 * @property string|null $value
 * @property string|null $description
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 */
class Configs extends \yii\db\ActiveRecord
{

    private static array $models = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'config';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['value', 'description'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 1],
            [['name'], 'required'],
            [['description'], 'string'],
            [['status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['value'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'value' => 'Value',
            'description' => 'Description',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    /**
     * Значення активного налаштування (або null). Кешується саме значення,
     * а не рядок таблиці, — однаково і при читанні з БД, і при set().
     */
    public static function get(string $name)
    {
        if (! array_key_exists($name, self::$models)) {
            self::$models[$name] = \Yii::$app->cache->getOrSet(self::cacheKey($name), static function () use ($name) {
                $value = self::find()
                    ->select('value')
                    ->where(['name' => $name, 'status' => 1])
                    ->scalar();

                // false = рядка немає; кешуємо null, щоб не ходити в БД щоразу
                return $value === false ? null : $value;
            });
        }

        return self::$models[$name];
    }


    public static function set(string $name, $value): void
    {
        $db = \Yii::$app->db;
        $exists = self::find()->where(['name' => $name])->exists();

        if ($exists) {
            $db->createCommand()->update(self::tableName(), ['value' => $value, 'status' => 1], ['name' => $name])->execute();
        } else {
            $db->createCommand()->insert(self::tableName(), ['name' => $name, 'value' => $value, 'status' => 1])->execute();
        }

        \Yii::$app->cache->delete(self::cacheKey($name));
        self::$models[$name] = $value;
    }


    private static function cacheKey(string $name): array
    {
        return [self::class, 'v2', $name];
    }
}
