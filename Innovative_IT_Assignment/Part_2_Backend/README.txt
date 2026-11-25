================================================================================
SMART STUDENT MANAGEMENT SYSTEM - PHP & MYSQL CRUD (PART 2 - BACKEND)
================================================================================

1. PROJECT TITLE
   Smart Student Management System - PHP and MySQL CRUD Application

2. STUDENT NAME
   Abhishek Kumar

3. ROLL NUMBER
   202610101050043 (MCA)

4. PROJECT INTRODUCTION
   Smart Student Management System is a full-stack, database-driven web
   application built with Core PHP, PDO, MySQL, and Bootstrap 5. It enables an
   administrator to create, view, search, filter, update, and delete student
   records with secure server-side validation and profile photo file handling.

5. OBJECTIVES
   - Build a modular PHP and MySQL web application.
   - Implement complete CRUD (Create, Read, Update, Delete) operations.
   - Use PDO with prepared statements and parameter binding to prevent SQL
     injection.
   - Implement strict server-side validation and CSRF protection.
   - Implement secure image upload, replacement, and deletion in PHP.
   - Drive all dashboard statistics dynamically from MySQL queries.

6. TECHNOLOGIES USED
   - Frontend: HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons, Vanilla JavaScript
   - Backend: Core PHP (8.x recommended), PDO (PHP Data Objects)
   - Database: MySQL / MariaDB (InnoDB, utf8mb4)
   - Local Server: XAMPP (Apache + MySQL + phpMyAdmin)

7. FEATURES
   - Dynamic Admin Dashboard (Total Students, Active Courses, Latest
     Registration, Course Distribution progress bars, Recently Added table,
     and MySQL-backed Activity Log).
   - Student Registration Form with 9 fields and live image preview.
   - Server-side validation with field-specific error messages and old-input
     preservation.
   - Student Directory Listing with search (name/roll/email), course filter,
     and pagination.
   - Student Details Page displaying complete personal, academic, and contact
     information.
   - Edit Student Form supporting profile photo replacement while preserving
     the existing photo when no new file is uploaded.
   - Delete Student with confirmation dialog/modal and automatic cleanup of
     the associated uploaded image file from disk.
   - Secure File Handling: MIME verification (finfo), extension whitelist
     (JPG, JPEG, PNG, WEBP), 2 MB limit, unique filenames, and .htaccess
     protection in uploads/students/.

8. FOLDER STRUCTURE
   Part_2_Backend/
   |-- index.php                  (Admin Dashboard)
   |-- database.sql               (Database & table schema + 12 sample rows)
   |-- README.txt
   |-- config/
   |   |-- database.php           (Centralized PDO connection)
   |-- includes/
   |   |-- header.php             (Shared HTML head, topbar, flash alerts)
   |   |-- sidebar.php            (Shared responsive sidebar)
   |   |-- footer.php             (Shared footer & JS scripts)
   |   |-- helpers.php            (Escaping, URLs, CSRF, flash, file upload)
   |   |-- validation.php         (Server-side input validation)
   |-- students/
   |   |-- index.php              (Student listing, search, filter, pagination)
   |   |-- add.php                (Registration form)
   |   |-- view.php               (Student details page)
   |   |-- edit.php               (Edit form)
   |   |-- delete.php             (Dedicated delete confirmation page)
   |-- actions/
   |   |-- store.php              (POST handler: Create student)
   |   |-- update.php             (POST handler: Update student)
   |   |-- destroy.php            (POST handler: Delete student + photo)
   |-- uploads/
   |   |-- students/              (Uploaded profile photos + .htaccess)
   |-- assets/
   |   |-- css/style.css
   |   |-- js/script.js
   |   |-- images/
   |-- screenshots/               (Place genuine XAMPP screenshots here)

9. INSTALLATION STEPS (XAMPP)
   1. Install XAMPP (if not already installed) from https://www.apachefriends.org/
   2. Copy the Part_2_Backend folder into C:\xampp\htdocs\Part_2_Backend
   3. Open XAMPP Control Panel:
      - Click "Start" next to Apache.
      - Click "Start" next to MySQL.
   4. Import the database:
      - Open http://localhost/phpmyadmin in your browser.
      - Click the "Import" tab at the top.
      - Click "Choose File" and select:
        C:\xampp\htdocs\Part_2_Backend\database.sql
      - Click "Import" (or "Go") at the bottom.
      - Verify that database `student_management_db` and table `students`
        with 12 sample rows are created.

10. EXECUTION INSTRUCTIONS
   1. Ensure Apache and MySQL are running in XAMPP.
   2. Open your browser and visit:
      http://localhost/Part_2_Backend/
   3. Use the sidebar or top buttons to:
      - View Dashboard statistics
      - Open "All Students" to search, filter, and paginate
      - Click "Register Student" to add a record with a profile photo
      - View, Edit, or Delete any student record
   4. After verifying in XAMPP, capture the 10 required screenshots and save
      them in Part_2_Backend/screenshots/.

11. CONFIGURATION DETAILS
   Edit config/database.php if your MySQL credentials differ from XAMPP defaults:
   - DB_HOST: 127.0.0.1
   - DB_NAME: student_management_db
   - DB_USER: root
   - DB_PASS: "" (empty by default in XAMPP)
   - DB_CHARSET: utf8mb4

12. TROUBLESHOOTING
   - "Database Connection Error":
     Confirm MySQL is started in XAMPP and database.sql was imported.
   - "Upload directory is not writable":
     Ensure uploads/students/ exists and has write permissions.
   - "404 Not Found" on links:
     Access the app via http://localhost/Part_2_Backend/ (or inside a
     subfolder); app_base_url() in includes/helpers.php adapts automatically.
   - Bootstrap styles missing offline:
     Part 2 uses Bootstrap 5 CDN. Ensure internet access or download Bootstrap
     locally into assets/.
================================================================================
