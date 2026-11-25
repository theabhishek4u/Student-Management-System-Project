/**
 * Smart Student Directory - Frontend AJAX, Registration & Activity Log
 * Uses jQuery AJAX ($.ajax / $.getJSON) to load students.json,
 * supports live search, course filtering, student registration, and an activity log.
 */
$(document).ready(function () {
  var STORAGE_STUDENTS_KEY = "ssd_registered_students_v1";
  var STORAGE_LOG_KEY = "ssd_activity_log_v1";

  var allStudents = [];
  var activityLogs = [];
  var currentView = "cards";
  var searchTimer = null;

  var $searchInput = $("#searchInput");
  var $clearSearchBtn = $("#clearSearchBtn");
  var $courseFilter = $("#courseFilter");
  var $cardsContainer = $("#studentCardsContainer");
  var $tableContainer = $("#studentTableContainer");
  var $tableBody = $("#studentTableBody");
  var $recentGrid = $("#recentStudentsGrid");
  var $loadingState = $("#loadingState");
  var $errorState = $("#errorState");
  var $emptyState = $("#emptyState");
  var $resultsSummary = $("#resultsSummary");
  var $dataStatusBadge = $("#dataStatusBadge");
  var $activityLogList = $("#activityLogList");

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function escapeRegExp(text) {
    return text.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  function highlightText(text, query) {
    var safeText = escapeHtml(text);
    if (!query) {
      return safeText;
    }
    var regex = new RegExp("(" + escapeRegExp(query) + ")", "gi");
    return safeText.replace(regex, '<mark class="search-highlight">$1</mark>');
  }

  function setStatusBadge(text, isError) {
    var $textEl = $dataStatusBadge.find(".status-text");
    if ($textEl.length) {
      $textEl.text(text);
    } else {
      $dataStatusBadge.text(text);
    }
    $dataStatusBadge.toggleClass("is-error", Boolean(isError));
  }

  // Activity Log Helpers
  function loadLogsFromStorage() {
    try {
      var raw = localStorage.getItem(STORAGE_LOG_KEY);
      activityLogs = raw ? JSON.parse(raw) : [];
      if (!Array.isArray(activityLogs)) activityLogs = [];
    } catch (e) {
      activityLogs = [];
    }
  }

  function saveLogsToStorage() {
    try {
      localStorage.setItem(STORAGE_LOG_KEY, JSON.stringify(activityLogs.slice(0, 50)));
    } catch (e) {
      // Ignore storage quota errors
    }
  }

  function addActivityLog(action, message) {
    var now = new Date();
    var timeStr = now.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit", second: "2-digit" });
    activityLogs.unshift({
      action: action,
      message: message,
      time: timeStr
    });
    if (activityLogs.length > 50) {
      activityLogs = activityLogs.slice(0, 50);
    }
    saveLogsToStorage();
    renderActivityLogs();
  }

  function renderActivityLogs() {
    $activityLogList.empty();
    if (activityLogs.length === 0) {
      $activityLogList.append('<li class="log-empty">No activity recorded yet. Search, filter, or register a student.</li>');
      return;
    }
    $.each(activityLogs, function (_, item) {
      var tagClass = "log-tag";
      if (item.action === "REGISTER") tagClass += " log-tag-register";
      if (item.action === "ERROR") tagClass += " log-tag-error";

      var li =
        '<li class="log-item">' +
          '<div class="log-item-left">' +
            '<span class="' + tagClass + '">' + escapeHtml(item.action) + '</span>' +
            '<span class="log-message">' + escapeHtml(item.message) + '</span>' +
          '</div>' +
          '<span class="log-time">' + escapeHtml(item.time) + '</span>' +
        '</li>';
      $activityLogList.append(li);
    });
  }

  // LocalStorage Registered Students Helpers
  function getCustomStudents() {
    try {
      var raw = localStorage.getItem(STORAGE_STUDENTS_KEY);
      var parsed = raw ? JSON.parse(raw) : [];
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }

  function saveCustomStudent(student) {
    var existing = getCustomStudents();
    existing.unshift(student);
    try {
      localStorage.setItem(STORAGE_STUDENTS_KEY, JSON.stringify(existing));
    } catch (e) {
      // Ignore storage errors
    }
  }

  function generateAvatarDataUri(name) {
    var parts = $.trim(name).split(/\s+/);
    var initials = ((parts[0] ? parts[0][0] : "S") + (parts[1] ? parts[1][0] : "")).toUpperCase();
    var svg =
      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120">' +
        '<rect width="120" height="120" rx="60" fill="#171717"/>' +
        '<text x="50%" y="53%" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" ' +
        'font-family="Geist,Arial,sans-serif" font-size="42" font-weight="600">' + escapeHtml(initials) + '</text>' +
      '</svg>';
    return "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(svg);
  }

  function getDataSourceUrl() {
    var params = new URLSearchParams(window.location.search);
    if (params.get("simulate_error") === "1") {
      return "missing_students_file.json";
    }
    return "students.json";
  }

  function loadStudents() {
    var url = getDataSourceUrl();

    $.ajax({
      url: url,
      method: "GET",
      dataType: "json",
      cache: false,
      beforeSend: function () {
        $loadingState.prop("hidden", false);
        $errorState.prop("hidden", true);
        $emptyState.prop("hidden", true);
        $cardsContainer.empty();
        $tableBody.empty();
        $resultsSummary.text("Loading student records...");
      },
      success: function (data) {
        if (!Array.isArray(data)) {
          showErrorState("Invalid JSON structure: expected an array of students.");
          return;
        }
        var customStudents = getCustomStudents();
        allStudents = customStudents.concat(data);
        setStatusBadge("AJAX Loaded (" + allStudents.length + ")", false);
        populateCourseFilter(allStudents);
        updateDashboardStats(allStudents, allStudents);
        renderRecentStudents(allStudents);
        applyFilters();
        addActivityLog("AJAX LOAD", "Loaded " + data.length + " records from students.json" + (customStudents.length ? " + " + customStudents.length + " registered" : "") + ".");
      },
      error: function (xhr, status, err) {
        var msg = "Failed to load " + url + " (Status: " + (xhr.status || status) + ").";
        if (err) {
          msg += " " + err;
        }
        showErrorState(msg);
        addActivityLog("ERROR", msg);
      },
      complete: function () {
        $loadingState.prop("hidden", true);
      }
    });
  }

  function showErrorState(message) {
    setStatusBadge("Load Error", true);
    $("#errorMessage").text(message);
    $errorState.prop("hidden", false);
    $emptyState.prop("hidden", true);
    $cardsContainer.empty();
    $tableContainer.prop("hidden", true);
    $resultsSummary.text("Unable to display student records.");
  }

  function populateCourseFilter(students) {
    var courses = [];
    $.each(students, function (_, student) {
      if (student.course && courses.indexOf(student.course) === -1) {
        courses.push(student.course);
      }
    });
    courses.sort();

    var currentVal = $courseFilter.val();
    $courseFilter.find("option:not(:first)").remove();
    var $pillsBar = $("#coursePillsBar");
    $pillsBar.find("button:not([data-course='all'])").remove();

    $.each(courses, function (_, course) {
      $courseFilter.append($("<option></option>").val(course).text(course));
      $pillsBar.append(
        $('<button type="button" class="category-pill"></button>').attr("data-course", course).text(course)
      );
    });
    if (currentVal && courses.indexOf(currentVal) !== -1) {
      $courseFilter.val(currentVal);
    }
    syncCoursePills($courseFilter.val());
  }

  function syncCoursePills(selectedCourse) {
    $("#coursePillsBar .category-pill").removeClass("active");
    $('#coursePillsBar .category-pill[data-course="' + selectedCourse + '"]').addClass("active");
  }

  function updateDashboardStats(students, filtered) {
    var uniqueCourses = [];
    var latestDate = "";

    $.each(students, function (_, s) {
      if (s.course && uniqueCourses.indexOf(s.course) === -1) {
        uniqueCourses.push(s.course);
      }
      if (s.enrollment_date && s.enrollment_date > latestDate) {
        latestDate = s.enrollment_date;
      }
    });

    $("#statTotalStudents").text(students.length);
    $("#statTotalCourses").text(uniqueCourses.length);
    $("#statMatchingResults").text(filtered.length);
    $("#statLatestEnrollment").text(latestDate || "N/A");
  }

  function renderRecentStudents(students) {
    var sorted = students.slice().sort(function (a, b) {
      return (b.enrollment_date || "").localeCompare(a.enrollment_date || "");
    });
    var recent = sorted.slice(0, 3);
    $recentGrid.empty();

    $.each(recent, function (_, student) {
      var cardHtml =
        '<article class="recent-card" data-id="' + escapeHtml(student.id) + '" tabindex="0">' +
          '<img src="' + escapeHtml(student.profile_image) + '" alt="' + escapeHtml(student.name) + '">' +
          '<div class="recent-info">' +
            '<h3>' + escapeHtml(student.name) + '</h3>' +
            '<p>' + escapeHtml(student.roll_number) + ' &middot; ' + escapeHtml(student.course) + '</p>' +
            '<p>Enrolled: ' + escapeHtml(student.enrollment_date) + '</p>' +
          '</div>' +
        '</article>';
      $recentGrid.append(cardHtml);
    });
  }

  function applyFilters() {
    var rawQuery = $.trim($searchInput.val());
    var query = rawQuery.toLowerCase();
    var selectedCourse = $courseFilter.val();

    $clearSearchBtn.prop("hidden", rawQuery.length === 0);

    var filtered = $.grep(allStudents, function (student) {
      var matchesCourse = selectedCourse === "all" || student.course === selectedCourse;
      if (!matchesCourse) {
        return false;
      }
      if (!query) {
        return true;
      }
      var nameMatch = (student.name || "").toLowerCase().indexOf(query) !== -1;
      var rollMatch = (student.roll_number || "").toLowerCase().indexOf(query) !== -1;
      var courseMatch = (student.course || "").toLowerCase().indexOf(query) !== -1;
      return nameMatch || rollMatch || courseMatch;
    });

    updateDashboardStats(allStudents, filtered);
    renderResults(filtered, rawQuery);
  }

  function renderResults(students, query) {
    $cardsContainer.empty();
    $tableBody.empty();

    if (students.length === 0) {
      $emptyState.prop("hidden", false);
      $cardsContainer.prop("hidden", true);
      $tableContainer.prop("hidden", true);
      $resultsSummary.text("Showing 0 of " + allStudents.length + " students");
      return;
    }

    $emptyState.prop("hidden", true);
    $resultsSummary.text("Showing " + students.length + " of " + allStudents.length + " students");

    if (currentView === "cards") {
      $cardsContainer.prop("hidden", false);
      $tableContainer.prop("hidden", true);
    } else {
      $cardsContainer.prop("hidden", true);
      $tableContainer.prop("hidden", false);
    }

    $.each(students, function (_, student) {
      var highlightedName = highlightText(student.name, query);
      var highlightedRoll = highlightText(student.roll_number, query);
      var highlightedCourse = highlightText(student.course, query);

      var cardHtml =
        '<article class="student-card">' +
          '<div class="student-card-head">' +
            '<div class="student-head-left">' +
              '<img class="student-avatar" src="' + escapeHtml(student.profile_image) + '" alt="' + escapeHtml(student.name) + '">' +
              '<div style="min-width:0;">' +
                '<h3 class="student-name">' + highlightedName + '</h3>' +
                '<p class="student-roll">' + highlightedRoll + '</p>' +
              '</div>' +
            '</div>' +
            '<span class="course-pill">' + highlightedCourse + '</span>' +
          '</div>' +
          '<div class="student-meta">' +
            '<div class="student-meta-item">' +
              '<span class="student-meta-icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>' +
              '<span class="student-meta-text" title="' + escapeHtml(student.email) + '">' + escapeHtml(student.email) + '</span>' +
            '</div>' +
            '<div class="student-meta-item">' +
              '<span class="student-meta-icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>' +
              '<span class="student-meta-text">' + escapeHtml(student.phone) + '</span>' +
            '</div>' +
            '<div class="student-meta-item">' +
              '<span class="student-meta-icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>' +
              '<span class="student-meta-text">CGPA: <strong>' + escapeHtml(student.cgpa || "N/A") + '</strong> / 10</span>' +
            '</div>' +
          '</div>' +
          '<div class="student-card-footer">' +
            '<span class="student-enrolled">' +
              '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>' +
              escapeHtml(student.enrollment_date) +
            '</span>' +
            '<button type="button" class="btn btn-sm view-details-btn" data-id="' + escapeHtml(student.id) + '">View Details &rarr;</button>' +
          '</div>' +
        '</article>';
      $cardsContainer.append(cardHtml);

      var rowHtml =
        '<tr>' +
          '<td><div class="table-student-cell">' +
            '<img src="' + escapeHtml(student.profile_image) + '" alt="' + escapeHtml(student.name) + '">' +
            '<span>' + highlightedName + '</span>' +
          '</div></td>' +
          '<td>' + highlightedRoll + '</td>' +
          '<td><span class="course-pill">' + highlightedCourse + '</span></td>' +
          '<td><strong>' + escapeHtml(student.cgpa || "N/A") + '</strong></td>' +
          '<td>' + escapeHtml(student.email) + '</td>' +
          '<td>' + escapeHtml(student.phone) + '</td>' +
          '<td>' + escapeHtml(student.enrollment_date) + '</td>' +
          '<td><button type="button" class="btn btn-sm view-details-btn" data-id="' + escapeHtml(student.id) + '">View</button></td>' +
        '</tr>';
      $tableBody.append(rowHtml);
    });
  }

  function openStudentModal(studentId) {
    var student = null;
    $.each(allStudents, function (_, item) {
      if (String(item.id) === String(studentId)) {
        student = item;
        return false;
      }
    });
    if (!student) {
      return;
    }

    var modalHtml =
      '<div class="modal-profile-top">' +
        '<div class="modal-profile-identity">' +
          '<img src="' + escapeHtml(student.profile_image) + '" alt="' + escapeHtml(student.name) + '">' +
          '<div style="min-width:0;">' +
            '<h4 id="modalStudentName" style="margin:0 0 6px;font-size:18px;font-weight:600;letter-spacing:-0.36px;">' + escapeHtml(student.name) + '</h4>' +
            '<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">' +
              '<span class="course-pill">' + escapeHtml(student.course) + '</span>' +
              '<span class="student-roll" style="margin:0;">' + escapeHtml(student.roll_number) + '</span>' +
            '</div>' +
          '</div>' +
        '</div>' +
        '<div class="modal-cgpa-badge">' +
          '<small>CGPA</small>' +
          '<strong>' + escapeHtml(student.cgpa || "N/A") + '</strong>' +
        '</div>' +
      '</div>' +
      '<div class="modal-details-list">' +
        '<div class="modal-detail-item"><label>Student ID</label><span>#' + escapeHtml(student.id) + '</span></div>' +
        '<div class="modal-detail-item"><label>Roll Number</label><span>' + escapeHtml(student.roll_number) + '</span></div>' +
        '<div class="modal-detail-item is-full"><label>Email Address</label><span>' + escapeHtml(student.email) + '</span></div>' +
        '<div class="modal-detail-item"><label>Phone Number</label><span>' + escapeHtml(student.phone) + '</span></div>' +
        '<div class="modal-detail-item"><label>Enrollment Date</label><span>' + escapeHtml(student.enrollment_date) + '</span></div>' +
      '</div>';

    $("#modalBody").html(modalHtml);
    $("#studentModal").prop("hidden", false);
    addActivityLog("VIEW", "Viewed profile of " + student.name + " (" + student.roll_number + ").");
  }

  function closeStudentModal() {
    $("#studentModal").prop("hidden", true);
  }

  // Registration Modal Logic
  function openRegisterModal() {
    $("#registerStudentForm")[0].reset();
    $(".field-error").text("");
    $(".form-input").removeClass("is-invalid");
    $("#registerFormAlert").prop("hidden", true).text("");
    var today = new Date().toISOString().slice(0, 10);
    $("#regDate").val(today);
    $("#registerModal").prop("hidden", false);
    $("#regName").focus();
  }

  function closeRegisterModal() {
    $("#registerModal").prop("hidden", true);
  }

  $("#registerStudentForm").on("submit", function (e) {
    e.preventDefault();
    $(".field-error").text("");
    $(".form-input").removeClass("is-invalid");
    $("#registerFormAlert").prop("hidden", true);

    var name = $.trim($("#regName").val());
    var roll = $.trim($("#regRoll").val()).toUpperCase();
    var email = $.trim($("#regEmail").val());
    var phone = $.trim($("#regPhone").val());
    var course = $("#regCourse").val();
    var enrollDate = $("#regDate").val();
    var cgpaRaw = $.trim($("#regCgpa").val());
    var cgpaNum = parseFloat(cgpaRaw);

    var hasError = false;

    if (name.length < 2) {
      $("#errRegName").text("Full name must be at least 2 characters.");
      $("#regName").addClass("is-invalid");
      hasError = true;
    }

    if (!/^[A-Z0-9_-]{2,30}$/.test(roll)) {
      $("#errRegRoll").text("Use 2-30 alphanumeric characters, hyphens, or underscores.");
      $("#regRoll").addClass("is-invalid");
      hasError = true;
    } else {
      var duplicate = $.grep(allStudents, function (s) {
        return (s.roll_number || "").toUpperCase() === roll;
      });
      if (duplicate.length > 0) {
        $("#errRegRoll").text("This roll number is already registered.");
        $("#regRoll").addClass("is-invalid");
        hasError = true;
      }
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      $("#errRegEmail").text("Please enter a valid email address.");
      $("#regEmail").addClass("is-invalid");
      hasError = true;
    }

    var digits = phone.replace(/\D/g, "");
    if (digits.length < 7 || digits.length > 15) {
      $("#errRegPhone").text("Enter a valid phone number (7-15 digits).");
      $("#regPhone").addClass("is-invalid");
      hasError = true;
    }

    if (!course) {
      $("#errRegCourse").text("Please select a course.");
      $("#regCourse").addClass("is-invalid");
      hasError = true;
    }

    if (!enrollDate) {
      $("#errRegDate").text("Enrollment date is required.");
      $("#regDate").addClass("is-invalid");
      hasError = true;
    }

    if (cgpaRaw === "" || isNaN(cgpaNum) || cgpaNum < 0 || cgpaNum > 10) {
      $("#errRegCgpa").text("Enter a valid CGPA between 0.00 and 10.00.");
      $("#regCgpa").addClass("is-invalid");
      hasError = true;
    }

    if (hasError) {
      $("#registerFormAlert").text("Please fix the highlighted fields before submitting.").prop("hidden", false);
      return;
    }

    var maxId = 0;
    $.each(allStudents, function (_, s) {
      if (Number(s.id) > maxId) maxId = Number(s.id);
    });

    var newStudent = {
      id: maxId + 1,
      name: name,
      roll_number: roll,
      email: email,
      phone: phone,
      course: course,
      cgpa: cgpaNum.toFixed(2),
      profile_image: generateAvatarDataUri(name),
      enrollment_date: enrollDate
    };

    saveCustomStudent(newStudent);
    allStudents.unshift(newStudent);
    setStatusBadge("AJAX Loaded (" + allStudents.length + ")", false);
    populateCourseFilter(allStudents);
    renderRecentStudents(allStudents);
    applyFilters();
    closeRegisterModal();
    addActivityLog("REGISTER", "Registered " + name + " (" + roll + ") in " + course + ".");
  });

  function resetAllFilters() {
    $searchInput.val("");
    $courseFilter.val("all");
    syncCoursePills("all");
    applyFilters();
    $searchInput.focus();
    addActivityLog("RESET", "Cleared search query and course filter.");
  }

  // Event Handlers
  $searchInput.on("input", function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function () {
      applyFilters();
      var q = $.trim($searchInput.val());
      if (q.length > 0) {
        addActivityLog("SEARCH", 'Searched directory for "' + q + '".');
      }
    }, 180);
  });

  $courseFilter.on("change", function () {
    var c = $courseFilter.val();
    syncCoursePills(c);
    applyFilters();
    addActivityLog("FILTER", "Filtered course: " + (c === "all" ? "All Courses" : c) + ".");
  });

  $(document).on("click", "#coursePillsBar .category-pill", function () {
    var course = $(this).data("course");
    $courseFilter.val(course).trigger("change");
  });

  $clearSearchBtn.on("click", function () {
    $searchInput.val("");
    applyFilters();
    $searchInput.focus();
  });

  $("#resetAllBtn, #emptyResetBtn").on("click", resetAllFilters);

  $("#refreshDataBtn, #retryLoadBtn").on("click", function () {
    if (window.location.search.indexOf("simulate_error=1") !== -1) {
      $.getJSON("students.json", function (data) {
        var customStudents = getCustomStudents();
        allStudents = customStudents.concat(data);
        $errorState.prop("hidden", true);
        setStatusBadge("AJAX Loaded (" + allStudents.length + ")", false);
        populateCourseFilter(allStudents);
        updateDashboardStats(allStudents, allStudents);
        renderRecentStudents(allStudents);
        applyFilters();
        addActivityLog("AJAX LOAD", "Recovered via $.getJSON() (" + allStudents.length + " records).");
      }).fail(function () {
        loadStudents();
      });
      return;
    }
    loadStudents();
  });

  $(".view-btn").on("click", function () {
    $(".view-btn").removeClass("active");
    $(this).addClass("active");
    currentView = $(this).data("view");
    applyFilters();
  });

  $(document).on("click", ".view-details-btn, .recent-card", function () {
    openStudentModal($(this).data("id"));
  });

  $("#closeModalBtn, #closeModalFooterBtn").on("click", closeStudentModal);
  $("#studentModal").on("click", function (e) {
    if (e.target === this) closeStudentModal();
  });

  $("#openRegisterBtn, #heroRegisterBtn").on("click", openRegisterModal);
  $("#navSearchBtn").on("click", function () {
    $("#navMenu").removeClass("is-open");
    $("#navToggle").attr("aria-expanded", "false");
    var el = document.getElementById("directory");
    if (el) el.scrollIntoView({ behavior: "smooth" });
    setTimeout(function () {
      $searchInput.focus();
    }, 250);
  });
  $("#closeRegisterModalBtn, #cancelRegisterBtn").on("click", closeRegisterModal);
  $("#registerModal").on("click", function (e) {
    if (e.target === this) closeRegisterModal();
  });

  $("#clearLogBtn").on("click", function () {
    activityLogs = [];
    saveLogsToStorage();
    renderActivityLogs();
  });

  $(document).on("keydown", function (e) {
    if (e.key === "Escape") {
      closeStudentModal();
      closeRegisterModal();
    }
  });

  $("#navToggle").on("click", function () {
    var isOpen = $("#navMenu").toggleClass("is-open").hasClass("is-open");
    $(this).attr("aria-expanded", isOpen ? "true" : "false");
  });

  // Close mobile nav when clicking a link
  $(".nav-link").on("click", function () {
    $("#navMenu").removeClass("is-open");
    $("#navToggle").attr("aria-expanded", "false");
  });

  loadLogsFromStorage();
  renderActivityLogs();
  loadStudents();
});
