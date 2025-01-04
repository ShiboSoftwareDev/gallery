-- drop table users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    directory VARCHAR(255) NOT NULL
);

-- drop table images;
CREATE TABLE images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filepath VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
