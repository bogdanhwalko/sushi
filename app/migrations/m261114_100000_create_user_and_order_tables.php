<?php

use app\migrations\BaseMigration;

/**
 * Користувачі (адміни + клієнти) та замовлення.
 *
 * Акаунти admin і demo переносяться з колишнього config/admin_users.php
 * з тими самими ID та паролями (зберігаються лише bcrypt-хеші).
 *
 * Безпечна для повторного запуску: вже створені таблиці, індекси й ключі пропускаються
 * (на сервері попередня спроба могла впасти посередині — DDL у MySQL не відкочується).
 */
class m261114_100000_create_user_and_order_tables extends BaseMigration
{
    public function up()
    {
        /* ---------- user ---------- */
        if (!$this->tableExists('{{%user}}')) {
            $this->createTable('{{%user}}', [
                'id' => $this->primaryKey()->unsigned(),
                'username' => $this->string(64)->notNull()->unique(),
                'password_hash' => $this->string(255)->notNull(),
                'auth_key' => $this->string(32)->notNull(),
                'role' => $this->string(16)->notNull()->defaultValue('customer'),
                'last_name' => $this->string(100)->null()->comment('Прізвище'),
                'first_name' => $this->string(100)->null()->comment("Ім'я"),
                'middle_name' => $this->string(100)->null()->comment('По батькові'),
                'phone' => $this->string(20)->null(),
                'email' => $this->string(255)->null(),
                'city_id' => $this->refType('{{%city}}'),
                'status' => $this->tinyInteger(1)->notNull()->defaultValue(1),
                'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            ], self::TABLE_OPTIONS);
        } else {
            echo "    > таблиця user уже є — дотворюю відсутнє\n";
        }

        $this->createIndexIfMissing('idx_user_role', '{{%user}}', 'role');
        $this->addForeignKeyIfPossible('fk_user_city', '{{%user}}', 'city_id', '{{%city}}', 'id', 'SET NULL', 'CASCADE');

        $security = Yii::$app->security;
        $admins = [
            [100, 'admin', '$2y$13$MaCLnIVGLQFJ6Mvp4ZKZoevjGeyRK/rtkDhIbmzRAb8piOoBoeGpm'],
            [101, 'demo', '$2y$13$vY/iYd3ntUwSXP14cQ2U5OAh9VI.5jXHXpZaiKCX9lEIulud//9hO'],
        ];
        foreach ($admins as [$id, $username, $hash]) {
            $exists = (new \yii\db\Query())->from('{{%user}}')
                ->where(['or', ['id' => $id], ['username' => $username]])
                ->exists($this->db);
            if ($exists) {
                echo "    > акаунт $username уже є — пропускаю\n";
                continue;
            }
            $this->insert('{{%user}}', [
                'id' => $id,
                'username' => $username,
                'password_hash' => $hash,
                'auth_key' => $security->generateRandomString(32),
                'role' => 'admin',
            ]);
        }


        /* ---------- orders ---------- */
        if (!$this->tableExists('{{%orders}}')) {
            $this->createTable('{{%orders}}', [
                'id' => $this->primaryKey()->unsigned(),
                'user_id' => $this->refType('{{%user}}'),
                'city_id' => $this->refType('{{%city}}'),
                'city_name' => $this->string(100)->null()->comment('Назва міста на момент замовлення'),
                'customer_name' => $this->string(150)->notNull(),
                'phone' => $this->string(20)->notNull(),
                'total' => $this->integer()->unsigned()->notNull()->defaultValue(0),
                'status' => $this->tinyInteger(1)->notNull()->defaultValue(1),
                'telegram_sent' => $this->boolean()->notNull()->defaultValue(0),
                'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            ], self::TABLE_OPTIONS);
        }

        $this->createIndexIfMissing('idx_orders_user_created', '{{%orders}}', ['user_id', 'created_at']);
        $this->addForeignKeyIfPossible('fk_orders_user', '{{%orders}}', 'user_id', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKeyIfPossible('fk_orders_city', '{{%orders}}', 'city_id', '{{%city}}', 'id', 'SET NULL', 'CASCADE');


        /* ---------- order_item ---------- */
        if (!$this->tableExists('{{%order_item}}')) {
            $this->createTable('{{%order_item}}', [
                'id' => $this->primaryKey()->unsigned(),
                'order_id' => $this->refType('{{%orders}}', 'id', true),
                'product_id' => $this->refType('{{%product}}'),
                'product_name' => $this->string(255)->notNull()->comment('Назва товару на момент замовлення'),
                'price' => $this->integer()->unsigned()->notNull(),
                'qty' => $this->integer()->unsigned()->notNull(),
            ], self::TABLE_OPTIONS);
        }

        $this->addForeignKeyIfPossible('fk_order_item_order', '{{%order_item}}', 'order_id', '{{%orders}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKeyIfPossible('fk_order_item_product', '{{%order_item}}', 'product_id', '{{%product}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%order_item}}');
        $this->dropTable('{{%orders}}');
        $this->dropTable('{{%user}}');
    }
}
