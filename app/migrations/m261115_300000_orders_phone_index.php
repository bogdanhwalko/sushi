<?php

use app\migrations\BaseMigration;

/**
 * Індекс за телефоном у замовленнях: клієнтів без реєстрації групуємо й шукаємо за номером.
 * Безпечна для повторного запуску.
 */
class m261115_300000_orders_phone_index extends BaseMigration
{
    private const INDEX = 'idx_orders_phone_user';

    public function up()
    {
        $this->createIndexIfMissing(self::INDEX, '{{%orders}}', ['phone', 'user_id']);
    }

    public function down()
    {
        if ($this->indexExists('{{%orders}}', self::INDEX)) {
            $this->dropIndex(self::INDEX, '{{%orders}}');
        }
    }
}
