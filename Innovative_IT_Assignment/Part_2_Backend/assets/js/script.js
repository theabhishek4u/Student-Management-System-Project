/**
 * Smart Student Management System - Frontend UI Helpers
 */
document.addEventListener("DOMContentLoaded", function () {
  // Mobile sidebar toggle
  const sidebar = document.getElementById("appSidebar");
  const backdrop = document.getElementById("sidebarBackdrop");
  const toggleBtn = document.getElementById("sidebarToggleBtn");

  function closeSidebar() {
    if (sidebar) sidebar.classList.remove("show");
    if (backdrop) backdrop.classList.remove("show");
  }

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", function () {
      sidebar.classList.toggle("show");
      if (backdrop) backdrop.classList.toggle("show");
    });
  }

  if (backdrop) {
    backdrop.addEventListener("click", closeSidebar);
  }

  // Delete confirmation modal wiring
  const deleteModalEl = document.getElementById("deleteConfirmModal");
  if (deleteModalEl) {
    deleteModalEl.addEventListener("show.bs.modal", function (event) {
      const trigger = event.relatedTarget;
      if (!trigger) return;
      const studentId = trigger.getAttribute("data-student-id");
      const studentName = trigger.getAttribute("data-student-name");
      const idInput = document.getElementById("deleteStudentIdInput");
      const nameSpan = document.getElementById("deleteStudentNameText");
      if (idInput) idInput.value = studentId || "";
      if (nameSpan) nameSpan.textContent = studentName || "this student";
    });
  }

  // Live image preview on file input
  const photoInput = document.getElementById("profile_photo");
  const previewImg = document.getElementById("photoPreviewImg");
  if (photoInput && previewImg) {
    photoInput.addEventListener("change", function () {
      const file = this.files && this.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function (e) {
        previewImg.src = e.target.result;
        previewImg.classList.remove("d-none");
      };
      reader.readAsDataURL(file);
    });
  }
});
