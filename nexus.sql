-- Run in phpMyAdmin or MySQL CLI
CREATE DATABASE nexus;
USE nexus;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


--each single news.
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


CREATE TABLE bookmarks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  news_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (news_id) REFERENCES news_card(id) ON DELETE CASCADE,
  UNIQUE (user_id, news_id) -- Prevents duplicate bookmarks
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



--Tracks anonymized behavioral data (scrolls, clicks, dwell time, shares).
CREATE TABLE user_behavior (
  id INT AUTO_INCREMENT PRIMARY KEY,
  news_id INT NOT NULL,
  session_id VARCHAR(64), --(no personal data)
  scroll_depth INT,
  dwell_time INT, -- in seconds
  clicked BOOLEAN DEFAULT FALSE,
  shared BOOLEAN DEFAULT FALSE,
  mood_hint VARCHAR(20), -- e.g., "skip", "engaged", "skim"
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (news_id) REFERENCES news_card(id) ON DELETE CASCADE
);


--Powers the “Why This News?” feature.
CREATE TABLE recommendation_reasons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  news_id INT NOT NULL,
  reason TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (news_id) REFERENCES news_card(id) ON DELETE CASCADE
);


-- For syncing offline behavior once reconnected.
CREATE TABLE offline_activity (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(64),
  news_id INT,
  action ENUM('read', 'clicked', 'shared'),
  timestamp TIMESTAMP,
  synced BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (news_id) REFERENCES news_card(id)
);

