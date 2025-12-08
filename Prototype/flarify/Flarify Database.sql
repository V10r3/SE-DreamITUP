CREATE DATABASE Flarify;
Use Flarify;

-- CREATE TABLE users (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   name VARCHAR(100),
--   email VARCHAR(100) UNIQUE,
--   password_hash VARCHAR(255),
--   role ENUM('developer','investor','tester')
-- );

-- CREATE TABLE projects (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   developer_id INT,
--   title VARCHAR(150),
--   description TEXT,
--   price DECIMAL(10,2),
--   file_path VARCHAR(255),
--   demo_flag BOOLEAN DEFAULT 0,
--   FOREIGN KEY (developer_id) REFERENCES users(id)
-- );

-- CREATE TABLE messages (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   sender_id INT,
--   receiver_id INT,
--   content TEXT,
--   timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--   FOREIGN KEY (sender_id) REFERENCES users(id),
--   FOREIGN KEY (receiver_id) REFERENCES users(id)
-- );

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('developer','investor','tester') NOT NULL DEFAULT 'developer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  developer_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) DEFAULT 0.00,
  file_path VARCHAR(255),
  demo_flag TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (developer_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender_id INT NOT NULL,
  receiver_id INT NOT NULL,
  content TEXT NOT NULL,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);