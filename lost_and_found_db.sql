CREATE DATABASE lost_and_found_db;

USE lost_and_found_db;


//users table
CREATE TABLE users
(
    id INT AUTO_INCREMENT PRIMARY KEY,

    fullname VARCHAR(100) NOT NULL,

    email VARCHAR(100) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    role ENUM(
        'user',
        'admin'
    ) DEFAULT 'user',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

//lost items table
CREATE TABLE lost_items
(
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    item_name VARCHAR(150) NOT NULL,

    category VARCHAR(100) NOT NULL,

    color VARCHAR(100),

    brand_model VARCHAR(150),

    unique_features TEXT,

    description TEXT NOT NULL,

    location_lost VARCHAR(255) NOT NULL,

    date_lost DATE NOT NULL,

    image VARCHAR(255),

    status ENUM(
        'pending',
        'matched',
        'claimed'
    ) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_lost_user
        FOREIGN KEY(user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

//found items table
CREATE TABLE found_items
(
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    item_name VARCHAR(150) NOT NULL,

    category VARCHAR(100) NOT NULL,

    color VARCHAR(100),

    brand_model VARCHAR(150),

    unique_features TEXT,

    description TEXT NOT NULL,

    location_found VARCHAR(255) NOT NULL,

    date_found DATE NOT NULL,

    image VARCHAR(255),

    status ENUM(
        'pending',
        'matched',
        'returned'
    ) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_found_user
        FOREIGN KEY(user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

//matches table
CREATE TABLE matches
(
    id INT AUTO_INCREMENT PRIMARY KEY,

    lost_item_id INT NOT NULL,

    found_item_id INT NOT NULL,

    confidence_score DECIMAL(5,2),

    status ENUM(
        'pending',
        'approved',
        'rejected'
    ) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_match_lost
        FOREIGN KEY(lost_item_id)
        REFERENCES lost_items(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_match_found
        FOREIGN KEY(found_item_id)
        REFERENCES found_items(id)
        ON DELETE CASCADE
);

//claims table
CREATE TABLE claims
(
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    match_id INT NOT NULL,

    claim_message TEXT NOT NULL,

    status ENUM(
        'pending',
        'approved',
        'rejected'
    ) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_claim_user
        FOREIGN KEY(user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_claim_match
        FOREIGN KEY(match_id)
        REFERENCES matches(id)
        ON DELETE CASCADE
);


//notifications table

CREATE TABLE notifications
(
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    message TEXT NOT NULL,

    is_read BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notification_user
        FOREIGN KEY(user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);


//expected tables


