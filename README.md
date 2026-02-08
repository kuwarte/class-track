# CLASS_TRACK

CLASS_TRACK is a web application created in PHP, ReactJS, and MySQL to manage your school subjects and learning entries.  
You can add subjects, create notes for each subject, and track your learning progress.

---

## Features

- Add, delete, and view subjects
- Add, delete, and view learning entries (topic + notes) per subject
- Simple and clean interface
- Built with **PHP**, **MySQL**, and **React** (Vite)

---

## Setup

### 1. Database

1. Create a MySQL database:

```sql
CREATE DATABASE class_track;
```

2. Import schema:

```sql
mysql -u root -p class_track < backend/sql/schema.sql
```

3. Configure database credentials in `./backend/config/config.php`:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'class_track');
```

### 2. Backend

1. Start the PHP development server:

```bash
cd backend/
php -S localhost:8000 public/index.php
```

### 3. Frontend

1. Install dependencies:

```bash
cd frontend/
pnpm install
```

2. Run the development server:

```bash
pnpm dev
```
