-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS parking_system;
USE parking_system;

-- Users/Cards table
CREATE TABLE IF NOT EXISTS users (
    uid INT PRIMARY KEY AUTO_INCREMENT,
    card_id VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Parking Sessions table
CREATE TABLE IF NOT EXISTS parking_sessions (
    session_id INT PRIMARY KEY AUTO_INCREMENT,
    card_id VARCHAR(50),
    entry_time DATETIME,
    exit_time DATETIME NULL,
    amount_charged DECIMAL(10, 2) NULL,
    status ENUM('active', 'completed') DEFAULT 'active',
    FOREIGN KEY (card_id) REFERENCES users(card_id)
);

-- Configuration table
CREATE TABLE IF NOT EXISTS config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rate_per_minute DECIMAL(10, 2) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default rate if not exists
INSERT INTO config (rate_per_minute) 
SELECT 100 
WHERE NOT EXISTS (SELECT 1 FROM config LIMIT 1);