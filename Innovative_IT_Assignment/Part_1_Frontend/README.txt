================================================================================
SMART STUDENT DIRECTORY - AJAX LIVE SEARCH SYSTEM (PART 1 - FRONTEND)
================================================================================

1. PROJECT TITLE
   Smart Student Directory - AJAX Live Search System

2. STUDENT NAME
   [Your Full Name]

3. ROLL NUMBER
   [Your Roll Number]

4. PROJECT INTRODUCTION
   Smart Student Directory is a responsive frontend web application built for
   the subject "Web Technology and Server-Side Scripting". It demonstrates
   asynchronous client-server communication by fetching student records from a
   local JSON file (students.json) using jQuery AJAX and filtering them live
   without reloading the webpage.

5. OBJECTIVES
   - Demonstrate practical frontend web development with HTML5, CSS3, and JS.
   - Implement asynchronous HTTP requests using jQuery $.ajax() and $.getJSON().
   - Parse and render structured JSON data dynamically in the DOM.
   - Provide instant, debounced search, course filtering, and search highlighting.
   - Handle loading, empty, and error states cleanly in a responsive UI.

6. TECHNOLOGIES USED
   - HTML5 (semantic structure)
   - CSS3 (CSS Grid, Flexbox, custom properties, responsive media queries)
   - JavaScript (ES5/ES6, debouncing, RegExp escaping, XSS-safe rendering)
   - jQuery 3.7.1 (bundled locally in assets/js/jquery-3.7.1.min.js)
   - AJAX ($.ajax() and $.getJSON())
   - JSON (students.json with 18 sample records)

7. FEATURES
   - Dynamic dashboard statistics (Total Students, Total Courses, Matching
     Results, Latest Enrollment Date).
   - Recently Enrolled Students preview section.
   - Live debounced search by student name, roll number, or course.
   - Case-insensitive matching with highlighted search terms (<mark>).
   - Quick course category pills + course filter dropdown combinable with
     text search.
   - Navbar Search button (scrolls to directory and focuses search input).
   - Student Registration modal with client-side validation, duplicate roll
     number check, auto-generated SVG initials avatar, and localStorage
     persistence.
   - Real-time Activity Log (AJAX LOAD, REGISTER, SEARCH, FILTER, VIEW, RESET)
     persisted in localStorage with Clear Log control.
   - Clear search button and Reset button.
   - Card Grid view and Table view toggle.
   - Student Profile Details modal on click.
   - Loading spinner, No Results empty state, and JSON load error state.

8. FOLDER STRUCTURE
   Part_1_Frontend/
   |-- index.html
   |-- students.json
   |-- README.txt
   |-- assets/
   |   |-- css/
   |   |   |-- style.css
   |   |-- js/
   |   |   |-- jquery-3.7.1.min.js
   |   |   |-- app.js
   |   |-- images/
   |       |-- avatar-01.svg ... avatar-18.svg
   |-- screenshots/
       |-- 01_main_dashboard.png
       |-- 02_student_search_interface.png
       |-- 03_live_search_results.png
       |-- 04_course_filtering.png
       |-- 05_no_results_found.png
       |-- 06_mobile_responsive_view.png

9. INSTALLATION STEPS
   No database or PHP is required for Part 1. However, AJAX requests to local
   JSON files require serving the folder over HTTP (browsers block AJAX on
   file:// URLs due to CORS security policy).

   Option A (XAMPP):
   1. Copy Part_1_Frontend into C:\xampp\htdocs\Part_1_Frontend.
   2. Open XAMPP Control Panel and start Apache.

   Option B (VS Code / Node / Python):
   1. Open Part_1_Frontend in terminal or VS Code.
   2. Start any static HTTP server (e.g., VS Code Live Server or `npx serve`).

10. EXECUTION INSTRUCTIONS
   1. Ensure your local HTTP server is running.
   2. Open a browser and navigate to:
      http://localhost/Part_1_Frontend/
   3. The dashboard will automatically load students.json via AJAX.
   4. Type a name, roll number, or course in the search bar, or use the
      course filter dropdown.
   5. To test the JSON loading error state (TC-10), visit:
      http://localhost/Part_1_Frontend/?simulate_error=1

11. CONFIGURATION DETAILS
   - Data file: students.json (18 records; add/edit objects keeping the same
     keys: id, name, roll_number, email, phone, course, profile_image,
     enrollment_date).
   - Search debounce delay: 180 ms (configured in assets/js/app.js).

12. TROUBLESHOOTING
   - Error "Unable to Load Student Data" when opening index.html directly:
     Do not double-click index.html (file://). Always open via http://localhost.
   - Blank page or $ is not defined:
     Verify assets/js/jquery-3.7.1.min.js exists and is readable.
   - Changes to students.json not showing:
     Perform a hard refresh (Ctrl + F5) in your browser.
================================================================================
