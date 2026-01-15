<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\FillForm;
use app\models\Tank;
use app\models\MilkLog;

class SiteController extends Controller
{
    /**
     * Отключаем layout для этого контроллера
     */
    public $layout = false;
    
    /**
     * Основное действие - отображает форму и статистику
     */
    public function actionIndex()
    {
        $model = new FillForm();
        $statistics = Tank::getStatistics();
        $history = MilkLog::getHistory(1, 10);

        return $this->render('index', [
            'model' => $model,
            'statistics' => $statistics,
            'history' => $history,
        ]);
    }

    /**
     * AJAX обработка заливки молока
     */
    public function actionFill()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        // Получаем данные из POST
        $personName = Yii::$app->request->post('person_name');
        $volume = (int)Yii::$app->request->post('volume');
        
        Yii::error("Получены данные: имя=$personName, объем=$volume");
        
        if (!$personName || $volume < 1 || $volume > 300) {
            Yii::error("Некорректные данные: имя=" . ($personName ?: 'пусто') . 
                    ", объем=$volume");
            return [
                'success' => false,
                'message' => 'Некорректные данные',
                'errors' => [
                    'person_name' => $personName ? [] : ['Имя обязательно'],
                    'volume' => $volume >= 1 && $volume <= 300 ? [] : ['Объем должен быть от 1 до 300 литров']
                ]
            ];
        }
        
        // Создаем запись через модель
        $result = MilkLog::createFillLog($personName, $volume);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Молоко успешно добавлено!',
                'statistics' => Tank::getStatistics(),
                'history' => MilkLog::getHistory(1, 10),
            ];
        } else {
            // Проверяем состояние цистерн для отладки
            $tanks = Tank::find()->all();
            $tankInfo = [];
            foreach ($tanks as $t) {
                $tankInfo[] = "Цистерна {$t->id}: {$t->current_volume}/{$t->max_volume} л";
            }
            
            Yii::error("Не удалось добавить молоко. Состояние цистерн: " . implode(', ', $tankInfo));
            
            return [
                'success' => false,
                'message' => 'Не удалось добавить молоко. Возможно, все цистерны заполнены.',
                'errors' => []
            ];
        }
    }

    /**
     * Получение статистики (AJAX)
     */
    public function actionGetStatistics()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        return [
            'success' => true,
            'statistics' => Tank::getStatistics(),
            'history' => MilkLog::getHistory(1, 10),
        ];
    }
}