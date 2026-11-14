<?php

use app\migrations\BaseMigration;

/**
 * Прив'язка кошика до акаунта клієнта.
 * Гостьовий кошик, як і раніше, визначається session_id (user_id = NULL).
 * Безпечна для повторного запуску.
 */
class m261114_200000_add_user_to_cart_item extends BaseMigration
{
    public function up()
    {
        if (!$this->columnExists('{{%cart_item}}', 'user_id')) {
            $after = $this->columnExists('{{%cart_item}}', 'session_id') ? ' AFTER `session_id`' : '';
            $this->addColumn('{{%cart_item}}', 'user_id', $this->refType('{{%user}}') . $after);
        }

        // один товар — один рядок у кошику користувача (NULL-и гостьових кошиків індекс не обмежує)
        $this->createIndexIfMissing('uq_cart_item_user_product', '{{%cart_item}}', ['user_id', 'product_id'], true);
        $this->createIndexIfMissing('idx_cart_item_session', '{{%cart_item}}', 'session_id');

        $this->addForeignKeyIfPossible('fk_cart_item_user', '{{%cart_item}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        if ($this->foreignKeyExists('{{%cart_item}}', 'fk_cart_item_user')) {
            $this->dropForeignKey('fk_cart_item_user', '{{%cart_item}}');
        }
        $this->dropIndex('idx_cart_item_session', '{{%cart_item}}');
        $this->dropIndex('uq_cart_item_user_product', '{{%cart_item}}');
        $this->dropColumn('{{%cart_item}}', 'user_id');
    }
}
