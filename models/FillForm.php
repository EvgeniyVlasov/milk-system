<?php

namespace app\models;

use Yii;
use yii\base\Model;

class FillForm extends Model
{
    public $person_name;
    public $volume;

    public function rules()
    {
        return [
            [['person_name', 'volume'], 'required'],
            [['person_name'], 'string', 'max' => 100],
            [['volume'], 'integer', 'min' => 1, 'max' => 300],
            ['volume', 'validateAvailableVolume'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'person_name' => 'Имя сотрудника',
            'volume' => 'Количество молока (л)',
        ];
    }

    public function validateAvailableVolume($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $totalCapacity = Tank::find()->sum('max_volume');
            $totalCurrent = Tank::find()->sum('current_volume');
            $available = $totalCapacity - $totalCurrent;
            
            if ($this->$attribute > $available) {
                $this->addError($attribute, 
                    "Доступно только {$available} литров. Слишком много молока для существующих цистерн.");
            }
        }
    }

    public function processFill()
    {
        if (!$this->validate()) {
            return false;
        }

        return MilkLog::createFillLog($this->person_name, $this->volume);
    }
}