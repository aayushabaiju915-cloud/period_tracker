-- ============================================================
-- Period Tracker Database Schema
-- Run this in phpMyAdmin > SQL tab
-- ============================================================

-- Create and select database
CREATE DATABASE IF NOT EXISTS period_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE period_tracker;

-- ============================================================
-- Table: users
-- Stores registered user accounts
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- bcrypt hashed
    dob         DATE,                           -- date of birth (optional)
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Table: cycles
-- Stores each menstrual cycle entry per user
-- ============================================================
CREATE TABLE IF NOT EXISTS cycles (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    start_date      DATE NOT NULL,
    end_date        DATE,                       -- nullable if cycle not ended yet
    cycle_length    INT DEFAULT 28,             -- days between periods
    period_length   INT DEFAULT 5,              -- days period lasts
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_cycles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- Table: symptoms
-- Tracks daily symptoms/mood linked to a cycle
-- ============================================================
CREATE TABLE IF NOT EXISTS symptoms (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    cycle_id    INT NOT NULL,
    log_date    DATE NOT NULL,
    mood        ENUM('happy','neutral','sad','anxious','irritable','energetic') DEFAULT 'neutral',
    flow        ENUM('none','light','medium','heavy','spotting') DEFAULT 'none',
    cramps      TINYINT(1) DEFAULT 0,           -- 1 = yes, 0 = no
    headache    TINYINT(1) DEFAULT 0,
    bloating    TINYINT(1) DEFAULT 0,
    fatigue     TINYINT(1) DEFAULT 0,
    notes       TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_symptoms_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    CONSTRAINT fk_symptoms_cycle FOREIGN KEY (cycle_id) REFERENCES cycles(id) ON DELETE CASCADE
);

-- ============================================================
-- Sample Test Data (optional — uncomment to insert)
-- Password for both test users: "Test@1234"
-- ============================================================

/*
INSERT INTO users (name, email, password, dob) VALUES
('Priya Sharma',  'priya@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1998-04-15'),
('Anita Koirala', 'anita@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2000-07-22');

INSERT INTO cycles (user_id, start_date, end_date, cycle_length, period_length, notes) VALUES
(1, '2025-11-01', '2025-11-05', 28, 5, 'Normal cycle'),
(1, '2025-11-29', '2025-12-03', 28, 5, 'Slight cramps'),
(1, '2025-12-27', '2025-12-31', 28, 5, 'Heavy flow on day 2'),
(1, '2026-01-24', '2026-01-28', 28, 5, 'Normal'),
(1, '2026-02-21', '2026-02-25', 28, 5, 'Mild headache');

INSERT INTO symptoms (user_id, cycle_id, log_date, mood, flow, cramps, headache, bloating, fatigue) VALUES
(1, 1, '2025-11-01', 'sad',     'medium', 1, 0, 1, 1),
(1, 1, '2025-11-02', 'neutral', 'heavy',  1, 1, 0, 1),
(1, 1, '2025-11-03', 'neutral', 'medium', 0, 0, 0, 0),
(1, 2, '2025-11-29', 'anxious', 'light',  1, 0, 1, 0),
(1, 5, '2026-02-21', 'happy',   'medium', 0, 1, 0, 1);
*/
