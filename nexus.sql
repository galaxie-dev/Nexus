-- Run in phpMyAdmin or MySQL CLI
CREATE DATABASE nexus;
USE nexus;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE news_card (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  category ENUM('technology', 'sports', 'politics', 'entertainment', 'business', 'health', 'science', 'world', 'education', 'travel', 'environment',
  'finance', 'fashion', 'lifestyle', 'food', 'automotive', 'culture', 'crime', 'weather', 'opinion', 'other') NOT NULL,
  image_path VARCHAR(255),
  likes INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


--We will still not implement this in the prototype
CREATE TABLE comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  news_id INT,
  user_id INT,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (news_id) REFERENCES news_card(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
