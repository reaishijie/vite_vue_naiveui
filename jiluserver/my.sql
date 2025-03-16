-- 创建数据库
CREATE DATABASE record_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE record_system;

-- 创建用户表
CREATE TABLE users (
                       id INT PRIMARY KEY AUTO_INCREMENT,
                       username VARCHAR(50) NOT NULL UNIQUE,
                       password VARCHAR(255) NOT NULL,
                       email VARCHAR(100) NOT NULL,
                       avatar VARCHAR(255),
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 创建记录表
CREATE TABLE records (
                         id INT PRIMARY KEY AUTO_INCREMENT,
                         user_id INT NOT NULL,
                         content TEXT NOT NULL,
                         record_date DATE NOT NULL,
                         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                         FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 创建轮播图表
CREATE TABLE carousel (
                          id INT PRIMARY KEY AUTO_INCREMENT,
                          image_url VARCHAR(255) NOT NULL,
                          title VARCHAR(100),
                          link VARCHAR(255),
                          sort_order INT DEFAULT 0
);