<?php

namespace app\migrations;

use yii\db\Migration;

/**
 * Міграція, що переживає повторний запуск і відмінності схеми між серверами.
 *
 * DDL у MySQL не відкочується транзакцією: якщо міграція впала посередині,
 * створені таблиці й індекси лишаються. Тому кожен крок спершу перевіряє, чи він уже зроблений.
 *
 * Типи колонок для зовнішніх ключів беруться з колонки, на яку вони посилаються:
 * на різних серверах id старих таблиць може бути int / int unsigned / bigint.
 */
abstract class BaseMigration extends Migration
{
    protected const TABLE_OPTIONS = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';


    protected function tableExists(string $table): bool
    {
        return $this->db->getTableSchema($table, true) !== null;
    }


    protected function columnExists(string $table, string $column): bool
    {
        return $this->db->getTableSchema($table, true)?->getColumn($column) !== null;
    }


    /** Тип колонки в БД, напр. "int(10) unsigned" */
    protected function columnType(string $table, string $column): string
    {
        $col = $this->db->getTableSchema($table, true)?->getColumn($column);
        if ($col === null) {
            throw new \RuntimeException("Немає колонки $table.$column");
        }

        return $col->dbType;
    }


    /** Тип для нової колонки, що посилатиметься на $refTable.$refColumn (NULL дозволено) */
    protected function refType(string $refTable, string $refColumn = 'id', bool $notNull = false): string
    {
        return $this->columnType($refTable, $refColumn) . ($notNull ? ' NOT NULL' : ' NULL DEFAULT NULL');
    }


    protected function indexExists(string $table, string $name): bool
    {
        return (bool)$this->db->createCommand(
            'SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND INDEX_NAME = :n',
            [':t' => $this->rawName($table), ':n' => $name]
        )->queryScalar();
    }


    protected function foreignKeyExists(string $table, string $name): bool
    {
        return (bool)$this->db->createCommand(
            'SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND CONSTRAINT_NAME = :n
               AND CONSTRAINT_TYPE = \'FOREIGN KEY\'',
            [':t' => $this->rawName($table), ':n' => $name]
        )->queryScalar();
    }


    protected function isInnoDb(string $table): bool
    {
        $engine = $this->db->createCommand(
            'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t',
            [':t' => $this->rawName($table)]
        )->queryScalar();

        return strcasecmp((string)$engine, 'InnoDB') === 0;
    }


    protected function createIndexIfMissing(string $name, string $table, $columns, bool $unique = false): void
    {
        if ($this->indexExists($table, $name)) {
            echo "    > індекс $name уже є — пропускаю\n";
            return;
        }

        $this->createIndex($name, $table, $columns, $unique);
    }


    /**
     * Зовнішній ключ, якщо його ще немає і MySQL здатен його створити.
     * Тип колонки підганяється під тип колонки, на яку вона посилається.
     * Для таблиць не InnoDB (напр. MyISAM) ключ неможливий — пропускаємо з попередженням,
     * сайт працює й без нього.
     */
    protected function addForeignKeyIfPossible(
        string $name, string $table, string $column, string $refTable, string $refColumn = 'id',
        ?string $delete = null, ?string $update = null
    ): void {
        if ($this->foreignKeyExists($table, $name)) {
            echo "    > ключ $name уже є — пропускаю\n";
            return;
        }

        foreach ([$table, $refTable] as $t) {
            if (!$this->isInnoDb($t)) {
                echo "    ! таблиця {$this->rawName($t)} не InnoDB — зовнішній ключ $name неможливий, пропускаю\n";
                return;
            }
        }

        $this->matchColumnType($table, $column, $refTable, $refColumn);
        $this->addForeignKey($name, $table, $column, $refTable, $refColumn, $delete, $update);
    }


    /** Підганяє тип $table.$column під $refTable.$refColumn (зберігаючи NULL / NOT NULL) */
    protected function matchColumnType(string $table, string $column, string $refTable, string $refColumn = 'id'): void
    {
        $want = $this->columnType($refTable, $refColumn);
        if (self::normalizeType($this->columnType($table, $column)) === self::normalizeType($want)) {
            return;
        }

        $allowNull = $this->db->getTableSchema($table, true)->getColumn($column)->allowNull;
        $this->alterColumn($table, $column, $want . ($allowNull ? ' NULL DEFAULT NULL' : ' NOT NULL'));
    }


    /** "int(11) unsigned" і "int(10) unsigned" для ключа сумісні — ширина відображення не важлива */
    protected static function normalizeType(string $type): string
    {
        return trim(preg_replace('/\(\d+\)/', '', strtolower($type)));
    }


    protected function rawName(string $table): string
    {
        return $this->db->schema->getRawTableName($table);
    }
}
