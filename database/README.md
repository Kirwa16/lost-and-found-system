# Database Setup Guide

Follow these exact steps to set up your local database.

## 1. Open MySQL Terminal
Run this in your terminal:
```bash
sudo mysql

## create user

CREATE USER 'lost_admin'@'127.0.0.1' IDENTIFIED WITH mysql_native_password BY 'LostFound2026';
GRANT ALL PRIVILEGES ON lost_and_found_db.* TO 'lost_admin'@'127.0.0.1';
FLUSH PRIVILEGES;
EXIT;


### Update Backend Config
Open backend/config/database.php and make sure it looks exactly like this:

private $host = "127.0.0.1";
private $db_name = "lost_and_found_db";
private $username = "lost_admin";
private $password = "LostFound2026";