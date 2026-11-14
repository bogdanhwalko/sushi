<?php

use yii\db\Migration;

/**
 * Видалення товару більше не блокується позиціями в кошиках:
 * cart_item.product_id → product.id тепер ON DELETE CASCADE (було RESTRICT).
 *
 * Таблиця cart_item створена не міграцією, тож назву старого ключа
 * шукаємо в information_schema — на сервері вона може відрізнятись.
 */
class m261114_300000_cart_item_product_cascade extends Migration
{
    /** у межах одного ALTER нова назва має відрізнятись від старої */
    private const FK_CASCADE = 'fk_cart_item_product_cascade';
    private const FK_RESTRICT = 'fk_cart_item_product';

    public function up()
    {
        $this->replaceProductForeignKey(self::FK_CASCADE, 'CASCADE');
    }

    public function down()
    {
        $this->replaceProductForeignKey(self::FK_RESTRICT, 'RESTRICT');
    }

    /**
     * Старий ключ видаляється і новий додається одним ALTER TABLE:
     * таблиця перебудовується один раз, і немає моменту, коли ключа немає зовсім.
     */
    private function replaceProductForeignKey(string $newName, string $onDelete): void
    {
        $db = $this->db;
        $parts = [];

        foreach ($this->findProductForeignKeys() as $name) {
            $parts[] = 'DROP FOREIGN KEY ' . $db->quoteColumnName($name);
        }

        $parts[] = 'ADD CONSTRAINT ' . $db->quoteColumnName($newName)
            . ' FOREIGN KEY (' . $db->quoteColumnName('product_id') . ')'
            . ' REFERENCES ' . $db->quoteTableName('{{%product}}') . ' (' . $db->quoteColumnName('id') . ')'
            . " ON DELETE {$onDelete} ON UPDATE CASCADE";

        $this->execute('ALTER TABLE ' . $db->quoteTableName('{{%cart_item}}') . ' ' . implode(', ', $parts));
    }

    /** @return string[] назви зовнішніх ключів cart_item.product_id */
    private function findProductForeignKeys(): array
    {
        return $this->db->createCommand(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table
               AND COLUMN_NAME = :column
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [
                ':table' => $this->db->schema->getRawTableName('{{%cart_item}}'),
                ':column' => 'product_id',
            ]
        )->queryColumn();
    }
}
