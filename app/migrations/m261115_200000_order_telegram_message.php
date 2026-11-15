<?php

use app\migrations\BaseMigration;

/**
 * Замовлення ↔ повідомлення в Telegram: щоб оновлювати статус у вже надісланому повідомленні
 * і приймати замовлення кнопкою «Прийняте» прямо з групи.
 *  - telegram_chat_id / telegram_message_id — де лежить повідомлення;
 *  - telegram_group_id — група міста на момент надсилання (від неї залежить, яким ботом редагувати);
 *  - accepted_by / accepted_at — хто й коли прийняв (ім'я в Telegram або адміністратор).
 * Новий статус «Прийняте» (4) окремої зміни схеми не потребує.
 * Безпечна для повторного запуску.
 */
class m261115_200000_order_telegram_message extends BaseMigration
{
    private const COLUMNS = ['telegram_chat_id', 'telegram_message_id', 'telegram_group_id', 'accepted_by', 'accepted_at'];

    public function up()
    {
        $t = '{{%orders}}';
        $add = [
            'telegram_chat_id' => $this->string(32)->null()->after('telegram_sent')->comment('Чат, куди надіслано замовлення'),
            'telegram_message_id' => $this->bigInteger()->unsigned()->null()->after('telegram_chat_id')->comment('ID повідомлення в чаті'),
            'telegram_group_id' => $this->integer()->unsigned()->null()->after('telegram_message_id')->comment('Група міста на момент надсилання'),
            'accepted_by' => $this->string(100)->null()->after('status')->comment('Хто прийняв замовлення'),
            'accepted_at' => $this->timestamp()->null()->after('accepted_by')->comment('Коли прийняли'),
        ];
        foreach ($add as $column => $type) {
            if (!$this->columnExists($t, $column)) {
                $this->addColumn($t, $column, $type);
            }
        }
    }

    public function down()
    {
        foreach (array_reverse(self::COLUMNS) as $column) {
            if ($this->columnExists('{{%orders}}', $column)) {
                $this->dropColumn('{{%orders}}', $column);
            }
        }
    }
}
