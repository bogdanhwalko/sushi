<?php

use app\migrations\BaseMigration;

/**
 * Історія змін шапки сайту (адмінка → «Шапка сайту»): кожне збереження — знімок усіх
 * налаштувань у JSON, щоб можна було повернутись до будь-якого з них.
 * Безпечна для повторного запуску.
 */
class m261115_100000_create_hero_history extends BaseMigration
{
    public function up()
    {
        if (!$this->tableExists('{{%hero_history}}')) {
            $this->createTable('{{%hero_history}}', [
                'id' => $this->primaryKey()->unsigned(),
                'settings' => $this->text()->notNull()->comment('JSON: усі налаштування шапки'),
                'user_id' => $this->refType('{{%user}}') . " COMMENT 'Хто зберіг'",
                'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ], self::TABLE_OPTIONS);
        }

        $this->createIndexIfMissing('idx_hero_history_created', '{{%hero_history}}', 'created_at');
        $this->addForeignKeyIfPossible('fk_hero_history_user', '{{%hero_history}}', 'user_id', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%hero_history}}');
    }
}
