-- Создание таблицы цистерн
CREATE TABLE IF NOT EXISTS tank (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    current_volume INT DEFAULT 0,
    max_volume INT DEFAULT 300,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Создание таблицы журнала заливок
CREATE TABLE IF NOT EXISTS milk_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tank_id INT NOT NULL,
    person_name VARCHAR(100) NOT NULL,
    volume INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tank_id) REFERENCES tank(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Создание 5 цистерн
INSERT INTO tank (name, current_volume, max_volume) VALUES
('Цистерна 1', 0, 300),
('Цистерна 2', 0, 300),
('Цистерна 3', 0, 300),
('Цистерна 4', 0, 300),
('Цистерна 5', 0, 300);