<?php

use yii\db\Migration;

class m231215_000001_create_tanks_table extends Migration
{
    public function safeUp()
    {
        // Таблица цистерн
        $this->createTable('tank', [
            'id' => $this->primaryKey()->comment('ID цистерны'),
            'name' => $this->string(50)->notNull()->comment('Название цистерны'),
            'current_volume' => $this->integer()->defaultValue(0)->comment('Текущий объем молока'),
            'max_volume' => $this->integer()->defaultValue(300)->comment('Максимальная вместимость'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Таблица журнала заливок
        $this->createTable('milk_log', [
            'id' => $this->primaryKey()->comment('ID записи'),
            'tank_id' => $this->integer()->notNull()->comment('ID цистерны'),
            'person_name' => $this->string(100)->notNull()->comment('Имя сотрудника'),
            'volume' => $this->integer()->notNull()->comment('Объем залитого молока'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Добавляем внешние ключи
        $this->addForeignKey(
            'fk_milk_log_tank',
            'milk_log',
            'tank_id',
            'tank',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Создаем 5 цистерн
        $this->batchInsert('tank', ['name', 'current_volume', 'max_volume'], [
            ['Цистерна 1', 0, 300],
            ['Цистерна 2', 0, 300],
            ['Цистерна 3', 0, 300],
            ['Цистерна 4', 0, 300],
            ['Цистерна 5', 0, 300],
        ]);
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_milk_log_tank', 'milk_log');
        $this->dropTable('milk_log');
        $this->dropTable('tank');
    }
}