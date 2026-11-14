<?php

use yii\db\Migration;

/**
 * Прив'язка кошика до акаунта клієнта.
 * Гостьовий кошик, як і раніше, визначається session_id (user_id = NULL).
 */
class m261114_200000_add_user_to_cart_item extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%cart_item}}', 'user_id', $this->integer()->unsigned()->null()->after('session_id'));

        // один товар — один рядок у кошику користувача (NULL-и гостьових кошиків індекс не обмежує)
        $this->createIndex('uq_cart_item_user_product', '{{%cart_item}}', ['user_id', 'product_id'], true);
        $this->createIndex('idx_cart_item_session', '{{%cart_item}}', 'session_id');

        $this->addForeignKey('fk_cart_item_user', '{{%cart_item}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_cart_item_user', '{{%cart_item}}');
        $this->dropIndex('idx_cart_item_session', '{{%cart_item}}');
        $this->dropIndex('uq_cart_item_user_product', '{{%cart_item}}');
        $this->dropColumn('{{%cart_item}}', 'user_id');
    }
}
