<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Tank extends ActiveRecord
{
    public static function tableName()
    {
        return 'tank';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['current_volume', 'max_volume'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['current_volume'], 'default', 'value' => 0],
            [['max_volume'], 'default', 'value' => 300],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'current_volume' => 'Текущий объем',
            'max_volume' => 'Максимальный объем',
            'created_at' => 'Создана',
            'updated_at' => 'Обновлена',
        ];
    }

    public function getMilkLogs()
    {
        return $this->hasMany(MilkLog::class, ['tank_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public static function getStatistics()
    {
        return self::find()
            ->select([
                'id',
                'name',
                'current_volume',
                'max_volume',
                'ROUND(current_volume * 100.0 / max_volume, 1) as fill_percentage'
            ])
            ->orderBy(['id' => SORT_ASC])
            ->limit(5)
            ->asArray()
            ->all();
    }

    public static function findBestTankForFilling()
    {
        $tanks = self::find()->orderBy(['current_volume' => SORT_ASC])->all();
        
        foreach ($tanks as $tank) {
            // Проверяем что значения не NULL
            if ($tank->current_volume === null || $tank->max_volume === null) {
                continue;
            }
            
            // Проверяем что можно добавить хотя бы 1 литр
            if ($tank->current_volume < $tank->max_volume) {
                return $tank;
            }
        }
        
        return null;
    }

    public function canFill($volume)
    {
        return ($this->current_volume + $volume) <= $this->max_volume;
    }

    public function fill($volume)
    {
        if ($this->canFill($volume)) {
            $this->current_volume += $volume;
            return $this->save();
        }
        return false;
    }
}
