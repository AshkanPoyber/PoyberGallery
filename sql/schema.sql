-- =============================================
--  PoyberGallery — Database Schema
--  Author: MohammadReza Dalili ( AshkanPoyber )
-- =============================================

CREATE DATABASE IF NOT EXISTS poybergallery
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE poybergallery;

-- ---------- Users ----------
CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(30)  NOT NULL UNIQUE,
  email         VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  avatar        VARCHAR(255) DEFAULT NULL,
  bio           VARCHAR(300) DEFAULT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- Images ----------
CREATE TABLE IF NOT EXISTS images (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  title       VARCHAR(120) NOT NULL,
  description TEXT DEFAULT NULL,
  file_path   VARCHAR(255) NOT NULL,
  views       INT UNSIGNED DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ---------- Likes ----------
CREATE TABLE IF NOT EXISTS likes (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  image_id   INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
  FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
  UNIQUE KEY unique_like (user_id, image_id)
) ENGINE=InnoDB;

-- ---------- Comments ----------
CREATE TABLE IF NOT EXISTS comments (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  image_id   INT UNSIGNED NOT NULL,
  text       TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
  FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
  INDEX idx_image (image_id)
) ENGINE=InnoDB;

-- ---------- Follows ----------
CREATE TABLE IF NOT EXISTS follows (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  follower_id  INT UNSIGNED NOT NULL,
  following_id INT UNSIGNED NOT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (follower_id)  REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_follow (follower_id, following_id)
) ENGINE=InnoDB;