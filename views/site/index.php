<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Система учета молока</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .progress-bar-low { background-color: #28a745; }
        .progress-bar-medium { background-color: #ffc107; }
        .progress-bar-high { background-color: #dc3545; }
        .tank-card { transition: all 0.3s; }
        .tank-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        body { background-color: #f8f9fa; }
        .card { border-radius: 10px; border: none; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
        <span class="navbar-brand mb-0 h1">Система учета молока</span>
    </div>
</nav>

<div class="container">
    <!-- Форма добавления молока -->
    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Добавить молоко</h5>
        </div>
        <div class="card-body">
            <form id="fillForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="personName" class="form-label">Имя сотрудника</label>
                        <input type="text" class="form-control" id="personName" name="person_name" 
                               placeholder="Введите имя сотрудника" required>
                    </div>
                    <div class="col-md-4">
                        <label for="volume" class="form-label">Объем молока (л)</label>
                        <input type="number" class="form-control" id="volume" name="volume" 
                               min="1" max="300" placeholder="Количество литров" required>
                        <div class="form-text">Максимум 300 литров на цистерну</div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                            Добавить
                        </button>
                    </div>
                </div>
            </form>
            <div id="message" class="mt-3"></div>
        </div>
    </div>
    
    <!-- Статистика цистерн -->
    <div class="card shadow mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Статистика цистерн</h5>
        </div>
        <div class="card-body">
            <div class="row" id="tanksContainer">
                <?php foreach ($statistics as $tank): ?>
                <div class="col-lg mb-3">
                    <div class="card tank-card h-100">
                        <div class="card-body">
                            <h6 class="card-title text-center"><?= Html::encode($tank['name']) ?></h6>
                            <?php
                            $percentage = $tank['fill_percentage'];
                            $barClass = $percentage > 80 ? 'progress-bar-high' : 
                                       ($percentage > 50 ? 'progress-bar-medium' : 'progress-bar-low');
                            $free = $tank['max_volume'] - $tank['current_volume'];
                            ?>
                            <div class="progress mb-2" style="height: 25px;">
                                <div class="progress-bar <?= $barClass ?>" 
                                     role="progressbar" 
                                     style="width: <?= $percentage ?>%"
                                     aria-valuenow="<?= $percentage ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <strong><?= round($percentage) ?>%</strong>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="mb-2">
                                    <span class="badge bg-secondary"><?= $tank['current_volume'] ?> / <?= $tank['max_volume'] ?> л</span>
                                </div>
                                <div>
                                    <small class="text-muted">Свободно: <span class="badge bg-success"><?= $free ?> л</span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- История заливок -->
    <div class="card shadow">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">История заливок</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Дата и время</th>
                            <th>Цистерна</th>
                            <th>Сотрудник</th>
                            <th>Объем</th>
                        </tr>
                    </thead>
                    <tbody id="historyTable">
                        <?php if (!empty($history)): ?>
                            <?php foreach ($history as $log): ?>
                            <tr>
                                <td><?= date('d.m.Y H:i', strtotime($log->created_at)) ?></td>
                                <td><?= Html::encode($log->tank->name) ?></td>
                                <td><?= Html::encode($log->person_name) ?></td>
                                <td><span class="badge bg-primary"><?= $log->volume ?> л</span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">История заливок пуста</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Обработка формы
    $('#fillForm').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $btn = $('#submitBtn');
        const $message = $('#message');
        
        // Блокируем кнопку
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Обработка...');
        
        // Отправляем AJAX запрос
        $.ajax({
            url: '<?= Url::to(['site/fill']) ?>',
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showMessage('success', response.message);
                    updateTanks(response.statistics);
                    updateHistory(response.history);
                    $form[0].reset();
                } else {
                    let errors = '';
                    if (response.errors) {
                        for (let field in response.errors) {
                            errors += response.errors[field].join('<br>') + '<br>';
                        }
                    }
                    showMessage('danger', response.message + (errors ? '<br>' + errors : ''));
                }
            },
            error: function(xhr) {
                showMessage('danger', 'Ошибка соединения с сервером');
            },
            complete: function() {
                $btn.prop('disabled', false).html('Добавить');
            }
        });
    });
    
    // Функция показа сообщений
    function showMessage(type, text) {
        const $message = $('#message');
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        
        $message.html(`
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${text}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        // Автоскрытие для успешных сообщений
        if (type === 'success') {
            setTimeout(() => {
                $message.find('.alert').alert('close');
            }, 3000);
        }
    }
    
    // Обновление статистики цистерн
    function updateTanks(statistics) {
        let html = '';
        
        statistics.forEach(tank => {
            const percentage = tank.fill_percentage;
            let barClass = 'progress-bar-low';
            if (percentage > 80) barClass = 'progress-bar-high';
            else if (percentage > 50) barClass = 'progress-bar-medium';
            
            const free = tank.max_volume - tank.current_volume;
            
            html += `
                <div class="col-lg mb-3">
                    <div class="card tank-card h-100">
                        <div class="card-body">
                            <h6 class="card-title text-center">${tank.name}</h6>
                            <div class="progress mb-2" style="height: 25px;">
                                <div class="progress-bar ${barClass}" 
                                     role="progressbar" 
                                     style="width: ${percentage}%"
                                     aria-valuenow="${percentage}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <strong>${Math.round(percentage)}%</strong>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="mb-2">
                                    <span class="badge bg-secondary">${tank.current_volume} / ${tank.max_volume} л</span>
                                </div>
                                <div>
                                    <small class="text-muted">Свободно: <span class="badge bg-success">${free} л</span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#tanksContainer').html(html);
    }
    
    // Обновление истории
    function updateHistory(history) {
        let html = '';
        
        if (history && history.length > 0) {
            history.forEach(log => {
                const date = new Date(log.created_at);
                const formattedDate = date.toLocaleString('ru-RU', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                html += `
                    <tr>
                        <td>${formattedDate}</td>
                        <td>${escapeHtml(log.tank.name)}</td>
                        <td>${escapeHtml(log.person_name)}</td>
                        <td><span class="badge bg-primary">${log.volume} л</span></td>
                    </tr>
                `;
            });
        } else {
            html = '<tr><td colspan="4" class="text-center text-muted">История заливок пуста</td></tr>';
        }
        
        $('#historyTable').html(html);
    }
    
    // Экранирование HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Автообновление каждые 30 секунд
    setInterval(function() {
        $.getJSON('<?= Url::to(['site/get-statistics']) ?>', function(response) {
            if (response.success) {
                updateTanks(response.statistics);
                updateHistory(response.history);
            }
        });
    }, 30000);
});
</script>
</body>
</html>
