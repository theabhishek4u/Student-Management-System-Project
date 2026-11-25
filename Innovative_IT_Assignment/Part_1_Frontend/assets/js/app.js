/**
 * Smart Student Directory - Frontend AJAX & Live Search
 * Uses jQuery AJAX ($.ajax / $.getJSON) to load students.json
 * and provides debounced live search, course filtering, and highlighting.
 */
$(document).ready(function () {
  var allStudents = [];
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

  // Escape HTML to prevent XSS when rendering JSON fields
  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  // Escape RegExp special characters for safe search highlighting
  function escapeRegExp(text) {
    return text.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  // Highlight matched search term inside escaped text
  function highlightText(text, query) {
    var safeText = escapeHtml(text);
    if (!query) {
      return safeText;
    }
    var regex = new RegExp("(" + escapeRegExp(query) + ")", "gi");
    return safeText.replace(regex, '<mark class="search-highlight">$1</mark>');
  }

  // Determine JSON source (supports ?simulate_error=1 for TC-10 error testing)
  function getDataSourceUrl() {
    var params = new URLSearchParams(window.location.search);
    if (params.get("simulate_error") === "1") {
      return "missing_students_file.json";
    }
    return "students.json";
  }

  // Load students asynchronously using jQuery $.ajax()
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
        allStudents = data;
        $dataStatusBadge.text("AJAX Loaded (" + allStudents.length + ")").removeClass("is-error");
        populateCourseFilter(allStudents);
        updateDashboardStats(allStudents, allStudents);
        renderRecentStudents(allStudents);
        applyFilters();
      },
      error: function (xhr, status, err) {
        var msg = "Failed to load " + url + " (Status: " + (xhr.status || status) + ").";
        if (err) {
          msg += " " + err;
        }
        showErrorState(msg);
      },
      complete: function () {
        $loadingState.prop("hidden", true);
      }
    });
  }

  function showErrorState(message) {
    $dataStatusBadge.text("Load Error").addClass("is-error");
    $("#errorMessage").text(message);
    $errorState.prop("hidden", false);
    $emptyState.prop("hidden", true);
    $cardsContainer.empty();
    $tableContainer.prop("hidden", true);
    $resultsSummary.text("Unable to display student records.");
  }

  // Populate course dropdown dynamically from loaded JSON
  function populateCourseFilter(students) {
    var courses = [];
    $.each(students, function (_, student) {
      if (student.course && courses.indexOf(student.course) === -1) {
        courses.push(student.course);
      }
    });
    courses.sort();

    $courseFilter.find("option:not(:first)").remove();
    $.each(courses, function (_, course) {
      $courseFilter.append(
        $("<option></option>").val(course).text(course)
      );
    });
  }

  // Update top dashboard statistics dynamically
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

  // Render top 3 recently enrolled students
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

  // Filter students by search query (name, roll_number, course) and course dropdown
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
            '<img class="student-avatar" src="' + escapeHtml(student.profile_image) + '" alt="' + escapeHtml(student.name) + '">' +
            '<div>' +
              '<h3 class="student-name">' + highlightedName + '</h3>' +
              '<p class="student-roll">' + highlightedRoll + '</p>' +
            '</div>' +
          '</div>' +
          '<div class="student-meta">' +
            '<div class="student-meta-row"><span>Course</span><span><span class="course-pill">' + highlightedCourse + '</span></span></div>' +
            '<div class="student-meta-row"><span>Email</span><span>' + escapeHtml(student.email) + '</span></div>' +
            '<div class="student-meta-row"><span>Phone</span><span>' + escapeHtml(student.phone) + '</span></div>' +
          '</div>' +
          '<div class="student-card-footer">' +
            '<span class="student-roll">Enrolled ' + escapeHtml(student.enrollment_date) + '</span>' +
            '<button type="button" class="btn btn-outline btn-sm view-details-btn" data-id="' + escapeHtml(student.id) + '">View Details</button>' +
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
          '<td>' + escapeHtml(student.email) + '</td>' +
          '<td>' + escapeHtml(student.phone) + '</td>' +
          '<td>' + escapeHtml(student.enrollment_date) + '</td>' +
          '<td><button type="button" class="btn btn-outline btn-sm view-details-btn" data-id="' + escapeHtml(student.id) + '">View</button></td>' +
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
        '<img src="' + escapeHtml(student.profile_image) + '" alt="' + escapeHtml(student.name) + '">' +
        '<div>' +
          '<h4 id="modalStudentName" style="margin:0 0 4px;font-size:1.2rem;">' + escapeHtml(student.name) + '</h4>' +
          '<span class="course-pill">' + escapeHtml(student.course) + '</span>' +
        '</div>' +
      '</div>' +
      '<div class="modal-details-list">' +
        '<div class="modal-detail-item"><label>Student ID</label><span>#' + escapeHtml(student.id) + '</span></div>' +
        '<div class="modal-detail-item"><label>Roll Number</label><span>' + escapeHtml(student.roll_number) + '</span></div>' +
        '<div class="modal-detail-item"><label>Email</label><span>' + escapeHtml(student.email) + '</span></div>' +
        '<div class="modal-detail-item"><label>Phone</label><span>' + escapeHtml(student.phone) + '</span></div>' +
        '<div class="modal-detail-item"><label>Course</label><span>' + escapeHtml(student.course) + '</span></div>' +
        '<div class="modal-detail-item"><label>Enrollment Date</label><span>' + escapeHtml(student.enrollment_date) + '</span></div>' +
      '</div>';

    $("#modalBody").html(modalHtml);
    $("#studentModal").prop("hidden", false);
  }

  function closeStudentModal() {
    $("#studentModal").prop("hidden", true);
  }

  function resetAllFilters() {
    $searchInput.val("");
    $courseFilter.val("all");
    applyFilters();
    $searchInput.focus();
  }

  // Event Handlers
  $searchInput.on("input", function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 180);
  });

  $courseFilter.on("change", applyFilters);

  $clearSearchBtn.on("click", function () {
    $searchInput.val("");
    applyFilters();
    $searchInput.focus();
  });

  $("#resetAllBtn, #emptyResetBtn").on("click", resetAllFilters);

  $("#refreshDataBtn, #retryLoadBtn").on("click", function () {
    // Also demonstrate $.getJSON capability by resetting simulate_error if retrying
    if (window.location.search.indexOf("simulate_error=1") !== -1) {
      $.getJSON("students.json", function (data) {
        allStudents = data;
        $errorState.prop("hidden", true);
        $dataStatusBadge.text("AJAX Loaded (" + allStudents.length + ")").removeClass("is-error");
        populateCourseFilter(allStudents);
        updateDashboardStats(allStudents, allStudents);
        renderRecentStudents(allStudents);
        applyFilters();
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
    if (e.target === this) {
      closeStudentModal();
    }
  });
  $(document).on("keydown", function (e) {
    if (e.key === "Escape") {
      closeStudentModal();
    }
  });

  $("#navToggle").on("click", function () {
    var isOpen = $("#navMenu").toggleClass("is-open").hasClass("is-open");
    $(this).attr("aria-expanded", isOpen ? "true" : "false");
  });

  // Initial AJAX load
  loadStudents();
});
