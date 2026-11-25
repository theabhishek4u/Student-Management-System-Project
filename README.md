# Smart Student Management Suite

**Innovative IT Assessment – Web Technology and Server-Side Scripting**

A two-part academic web development suite featuring a **Frontend AJAX & JSON Student Directory** (Part 1) and a **Backend PHP & MySQL CRUD Management System** (Part 2), styled with the **Vercel Geist** monochrome design system.

---

## Project Structure

```text
Innovative_IT_Assignment/
├── Part_1_Frontend/              # Smart Student Directory (HTML5, CSS3, jQuery AJAX, JSON)
│   ├── index.html                # Main dashboard, search/filter, registration modal & activity log
│   ├── students.json             # 18 sample student records
│   ├── assets/
│   │   ├── css/style.css         # Geist design system + responsive styles
│   │   ├── js/jquery-3.7.1.min.js
│   │   ├── js/app.js             # AJAX loading, debounced search, registration & activity log
│   │   └── images/               # Custom SVG logo + 18 student avatars
│   ├── screenshots/              # Verified browser screenshots
│   └── README.txt
├── Part_2_Backend/               # Smart Student Management System (Core PHP, PDO, MySQL)
│   ├── index.php                 # Dynamic MySQL admin dashboard + activity log
│   ├── database.sql              # student_management_db schema, sample data & activity_logs
│   ├── config/database.php       # Centralized PDO connection (utf8mb4, prepared statements)
│   ├── includes/                 # Shared header, sidebar, footer, helpers & validation
│   ├── students/                 # List, Add, View, Edit, Delete pages
│   ├── actions/                  # POST handlers (store.php, update.php, destroy.php)
│   ├── uploads/students/         # Uploaded profile photos (.htaccess protected)
│   ├── assets/                   # Geist Bootstrap overrides, JS helpers & SVG logo
│   └── README.txt
└── VIVA_NOTES.txt                # Viva concepts, SQL explanations & Q&A
```

---

## Part 1: Smart Student Directory (Frontend)

- **Asynchronous Data Loading:** Fetches `students.json` via jQuery `$.ajax()` / `$.getJSON()` without page reloads.
- **Live Search & Filtering:** 180 ms debounced search by name, roll number, or course with `<mark>` highlighting, combinable with the course dropdown (`BCA`, `MCA`, `BBA`, `B.Tech`, `MBA`).
- **Student Registration & Activity Log:** Built-in registration modal with validation, duplicate roll number checks, SVG avatar generation, `localStorage` persistence, and a live Activity Log.
- **UI & States:** Card Grid / Table view toggle, profile details modal, loading spinner, empty state, and error recovery (`?simulate_error=1`).

### Running Part 1 Locally

> Do not open `index.html` directly via `file://` as browsers block local AJAX requests due to CORS.

1. Serve `Innovative_IT_Assignment/Part_1_Frontend` using any local HTTP server (XAMPP Apache, VS Code Live Server, or Node):
   ```bash
   npx serve Innovative_IT_Assignment/Part_1_Frontend
   ```
2. Open the served URL in your browser (e.g. `http://localhost:3000/` or `http://localhost/Part_1_Frontend/`).

---

## Part 2: Smart Student Management System (PHP & MySQL)

- **Dynamic Admin Dashboard:** Live MySQL counts for total students, active courses, latest registration, course distribution, and recent activity logs.
- **Complete CRUD:** Register, list (search, filter, pagination), view details, edit, and delete student records.
- **Security:** PDO prepared statements (`ATTR_EMULATE_PREPARES => false`), `htmlspecialchars()` output escaping, CSRF tokens on all POST forms, and strict server-side validation.
- **File Handling:** Profile photo uploads (`JPG`, `JPEG`, `PNG`, `WEBP`, max 2 MB) verified via `finfo_file()` MIME inspection, unique filenames, and automatic cleanup on update/delete.

### Running Part 2 Locally (XAMPP)

1. Install **XAMPP** and start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy `Innovative_IT_Assignment/Part_2_Backend` to `C:\xampp\htdocs\Part_2_Backend`.
3. Open `http://localhost/phpmyadmin`, go to **Import**, and import `Part_2_Backend/database.sql`.
4. Visit `http://localhost/Part_2_Backend/` in your browser.

---

## Documentation & Viva Preparation

- [Part 1 README](Innovative_IT_Assignment/Part_1_Frontend/README.txt)
- [Part 2 README](Innovative_IT_Assignment/Part_2_Backend/README.txt)
- [Viva Notes & Q&A](Innovative_IT_Assignment/VIVA_NOTES.txt)
