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


    public static function get(string $name)
    {
        if (! isset(self::$models[$name])) {
            if (\Yii::$app->cache->exists($name)) {
                self::$models[$name] = [
                    'value' => \Yii::$app->cache->get($name),
                    'status' => 1
                ];
            }
            else {
                self::$models[$name] = self::find()
                    ->select(['value', 'status'])
                    ->where(['name' => $name, 'status' => 1])
                    ->asArray()
                    ->one();

                if (isset(self::$models[$name]['status']) && self::$models[$name]['status']) {
                    \Yii::$app->cache->set($name, self::$models[$name]);
                }
            }
        }

        return self::$models[$name]['value'] ?? null;
    }


    public static function set(string $name, $value)
    {
        self::get($name);

        if (empty(self::$models[$name])) {
            self::$models[$name] = ['name' => $name, 'status' => 1, 'value' => $value];
            \Yii::$app->db->createCommand()->insert(self::tableName(), self::$models[$name])->execute();
        }
        else {
            \Yii::$app->db->createCommand()->update(self::tableName(), self::$models[$name], ['name' => $name])->execute();
            self::$models[$name]['value'] = $value;
        }

        \Yii::$app->cache->set($name, $value);
    }
}
