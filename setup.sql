-- AI Lifestyle Web App Database Setup

-- Create database
CREATE DATABASE IF NOT EXISTS ai_lifestyle;
USE ai_lifestyle;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  avatar_id VARCHAR(50) DEFAULT NULL,
  original_avatar_name VARCHAR(255) DEFAULT NULL,
  summary TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  google_id VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Widgets table
CREATE TABLE IF NOT EXISTS widgets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  short_text VARCHAR(255) NOT NULL,
  full_text TEXT,
  is_html BOOLEAN DEFAULT FALSE,
  media_type ENUM('none', 'image', 'video', 'weblink', 'map') DEFAULT 'none',
  media_content VARCHAR(255),
  original_file_name VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tags table
CREATE TABLE IF NOT EXISTS tags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default tags
INSERT IGNORE INTO tags (name) VALUES 
  ('lifestyle'),
  ('wellness'),
  ('mindfulness'),
  ('creativity'),
  ('productivity'),
  ('community'),
  ('learning'),
  ('nature'),
  ('technology'),
  ('travel');

-- Demo user for development
-- Password is 'demo123' (hashed with password_hash using PASSWORD_DEFAULT)
INSERT IGNORE INTO users (username, email, password, summary) VALUES
  ('demo', 'demo@example.com', '$2y$10$zF9/D8LUKuIQYLQwjUVVYeYTqGfzH6JKbSPQrVw.DlMYIxSLs2oCe', 'This is a demo account for development and testing purposes.');

-- Widget tags relation
CREATE TABLE IF NOT EXISTS widget_tags (
  widget_id INT NOT NULL,
  tag_id INT NOT NULL,
  PRIMARY KEY (widget_id, tag_id),
  FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Likes table
CREATE TABLE IF NOT EXISTS likes (
  user_id INT NOT NULL,
  widget_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, widget_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comments table
CREATE TABLE IF NOT EXISTS comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  widget_id INT NOT NULL,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Follows table
CREATE TABLE IF NOT EXISTS follows (
  follower_id INT NOT NULL,
  following_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (follower_id, following_id),
  FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create full-text search index on widgets
ALTER TABLE widgets ADD FULLTEXT (short_text, full_text);
