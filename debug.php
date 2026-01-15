<?php
echo "<pre>";
echo "PHP version: " . phpversion() . "\n";
echo "mb_internal_encoding: " . mb_internal_encoding() . "\n";
echo "default_charset: " . ini_get('default_charset') . "\n\n";

// Подключимся к базе
$pdo = new PDO('mysql:host=db;dbname=milk_db;charset=utf8mb4', 'user', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Проверим кодировку подключения
$stmt = $pdo->query("SHOW VARIABLES LIKE 'character_set_connection'");
$charset = $stmt->fetch(PDO::FETCH_ASSOC);
echo "MySQL connection charset: " . $charset['Value'] . "\n";

// Проверим данные
$stmt = $pdo->query("SELECT id, name, HEX(name) as hex_name FROM tank LIMIT 1");
$tank = $stmt->fetch(PDO::FETCH_ASSOC);

echo "\nПервая цистерна:\n";
echo "ID: " . $tank['id'] . "\n";
echo "Name: " . $tank['name'] . "\n";
echo "Hex: " . $tank['hex_name'] . "\n";

// Проверим, что hex соответствует UTF-8
$hex = $tank['hex_name'];
echo "\nHex анализ:\n";
echo "Hex: $hex\n";
echo "Expected for 'Ц': D0\n";
echo "Actual start: " . substr($hex, 0, 4) . "\n";
echo "</pre>";