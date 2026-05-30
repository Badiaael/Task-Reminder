CREATE DATABASE task_reminder;
USE task_reminder;
CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(100) NOT NULL,
email VARCHAR(150) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE tasks (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT,
title VARCHAR(255) NOT NULL,
description TEXT,
reminder_datetime DATETIME,
status ENUM('pending','completed') DEFAULT 'pending',
priority ENUM('low','medium','high') DEFAULT 'medium',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);