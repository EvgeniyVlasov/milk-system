-- Удаляем таблицы если они существуют
DROP TABLE IF EXISTS milk_log;
DROP TABLE IF EXISTS tank;

-- Создаем таблицу цистерн
CREATE TABLE tank (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    current_volume INT DEFAULT 0,
    max_volume INT DEFAULT 300,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Создаем таблицу журнала
CREATE TABLE milk_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tank_id INT NOT NULL,
    person_name VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    volume INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tank_id) REFERENCES tank(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Добавляем 5 цистерн
INSERT INTO tank (name, current_volume, max_volume) VALUES
('Цистерна 1', 0, 300),
('Цистерна 2', 0, 300),
('Цистерна 3', 0, 300),
('Цистерна 4', 0, 300),
('Цистерна 5', 0, 300);