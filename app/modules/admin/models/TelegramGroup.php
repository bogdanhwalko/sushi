<?php

namespace app\modules\admin\models;

use Yii;

/**
 * This is the model class for table "telegram_group".
 *
 * @property int $id
 * @property string $name
 * @property int|null $telegram_id
 * @property string|null $description
 * @property int $status
 * @property string|null $bot_id
 * @property string $created_at
 * @property string $updated_at
 */
class TelegramGroup extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'telegram_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'telegram_id'], 'required'],
            ['status', 'boolean'],
            [['telegram_id', 'description', 'bot_id'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 1],
            ['description', 'string'],
            [['created_at', 'updated_at'], 'safe'],
            ['name', 'string', 'max' => 100],
            ['bot_id', 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Назва',
            'telegram_id' => 'Telegram ID',
            'description' => 'Опис',
            'status' => 'Статус',
            'bot_id' => 'Токен',
            'created_at' => 'Створено',
            'updated_at' => 'Оновлено',
        ];
    }

}
