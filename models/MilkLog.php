<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class MilkLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'milk_log';
    }

    public function rules()
    {
        return [
            [['tank_id', 'person_name', 'volume'], 'required'],
            [['tank_id', 'volume'], 'integer'],
            [['volume'], 'integer', 'min' => 1],
            [['person_name'], 'string', 'max' => 100],
            [['tank_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tank::class, 'targetAttribute' => ['tank_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tank_id' => 'Цистерна',
            'person_name' => 'Сотрудник',
            'volume' => 'Объем (л)',
            'created_at' => 'Дата заливки',
        ];
    }

    public function getTank()
    {
        return $this->hasOne(Tank::class, ['id' => 'tank_id']);
    }

    public static function createFillLog($personName, $volume)
    {
        // Приводим объем к целому числу
        $volume = (int)$volume;
        
        // Находим лучшую цистерну для заполнения
        $tank = Tank::findBestTankForFilling();
        
        // Отладочный вывод (можно убрать позже)
        Yii::error("Найдена цистерна: " . ($tank ? $tank->id : 'нет') . 
                ", объем для добавления: $volume");
        
        if (!$tank) {
            Yii::error("Не найдена ни одна цистерна для заполнения");
            return false;
        }
        
        if (!$tank->canFill($volume)) {
            Yii::error("Цистерна {$tank->id} не может вместить $volume литров. " .
                    "Текущий объем: {$tank->current_volume}, Максимум: {$tank->max_volume}");
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Заполняем цистерну
            if (!$tank->fill($volume)) {
                throw new \Exception('Не удалось заполнить цистерну');
            }

            // Создаем запись в журнале
            $log = new self();
            $log->tank_id = $tank->id;
            $log->person_name = $personName;
            $log->volume = $volume;
            
            if (!$log->save()) {
                $errors = $log->getFirstErrors();
                throw new \Exception('Не удалось сохранить запись в журнале: ' . 
                                implode(', ', $errors));
            }

            $transaction->commit();
            
            Yii::error("Успешно добавлено $volume литров в цистерну {$tank->id}");
            return true;
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), 'milk-fill');
            return false;
        }
    }

    public static function getHistory($page = 1, $pageSize = 10)
    {
        $query = self::find()->with('tank')->orderBy(['created_at' => SORT_DESC]);
        
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
                'page' => $page - 1,
            ],
        ]);

        return $dataProvider->getModels();
    }
}