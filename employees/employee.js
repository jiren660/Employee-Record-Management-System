/**
 * JavaScript for Employee CRUD & API Interactions
 * Western Mindanao State University - College of Computing Studies
 * Reference: 03-Simple-Web-Application-Development-with-API-implementation.pdf (Pages 10, 13, 16, 19, 21)
 * Includes GitHub-style Delete Confirmation Modal (deletePrompt & handleConfirmDelete)
 */

// Determine API endpoint dynamically and robustly across all URL cases
const API_URL = (function() {
  const path = window.location.pathname.toLowerCase();
  if (path.includes('/ads133')) {
    const match = window.location.pathname.match(/\/ads133/i);
    const prefix = match ? window.location.pathname.substring(0, match.index + match[0].length) : '/ADS133';
    return prefix + '/api/employee_api.php';
  }
  return '../api/employee_api.php';
})();

const TEST_CONN_URL = (function() {
  const path = window.location.pathname.toLowerCase();
  if (path.includes('/ads133')) {
    const match = window.location.pathname.match(/\/ads133/i);
    const prefix = match ? window.location.pathname.substring(0, match.index + match[0].length) : '/ADS133';
    return prefix + '/api/test_connection.php';
  }
  return '../api/test_connection.php';
})();

// Currently viewed employee in details modal
let currentViewedEmployee = null;

// Delete Target ID Tracker (from GitHub repository)
let deleteTargetId = null;

// Initialize on DOM Ready
document.addEventListener("DOMContentLoaded", () => {
  loadEmployees();
  checkConnectionStatus(false);
  setupEventListeners();
});

/**
 * Check Database Connection Status
 */
async function checkConnectionStatus(interactive = false) {
  const statusSpan = document.getElementById("connectionStatus");
  const testBtn = document.getElementById("testConnectionBtn");

  if (testBtn) {
    testBtn.disabled = true;
    testBtn.textContent = "Testing...";
  }

  try {
    const res = await fetch(TEST_CONN_URL + "?t=" + new Date().getTime());
    const data = await res.json();

    if (res.ok && data.status === "success") {
      if (statusSpan) {
        statusSpan.textContent = "Database Connected Successfully";
        statusSpan.className = "status success";
      }

      if (interactive) {
        alert(
          `Database Connection Status: SUCCESS!\n\n` +
          `Database: ${data.database || 'test_connection_db'}\n` +
          `Server Version: ${data.server_version || 'MariaDB/MySQL'}\n` +
          `Total Employees: ${data.total_employees ?? 'N/A'}`
        );
        showToast("success", "Database Connected", `Connected to ${data.database || 'MySQL database'}.`);
      }
    } else {
      if (statusSpan) {
        statusSpan.textContent = "Database Connection Failed";
        statusSpan.className = "status error";
      }
      if (interactive) {
        alert(
          `Database Connection Status: FAILED\n\n` +
          `Error: ${data.message || 'Could not connect to database.'}`
        );
        showToast("error", "Database Disconnected", data.message || "Could not connect to database.");
      }
    }
  } catch (err) {
    console.error("Database connection check failed:", err);
    if (statusSpan) {
      statusSpan.textContent = "Database Connection Failed";
      statusSpan.className = "status error";
    }
    if (interactive) {
      alert("Database Connection Status: FAILED\nCould not reach test_connection.php endpoint.");
    }
  } finally {
    if (testBtn) {
      testBtn.disabled = false;
      testBtn.textContent = "Test DB";
    }
  }
}

/**
 * Interactive DB Connection Test Button Handler
 */
function runConnectionTest() {
  checkConnectionStatus(true);
}

/**
 * Fetch all employees and display them in the table (PDF 3 Page 13)
 * Clicking any table row opens the View Employee Profile Modal
 */
function loadEmployees() {
  fetch(API_URL + "?t=" + new Date().getTime())
    .then((response) => response.json())
    .then((data) => {
      const employeeTableBody = document.getElementById("employeeTableBody");
      const recordCount = document.getElementById("recordCount");
      const emptyState = document.getElementById("emptyState");

      if (!employeeTableBody) return;
      employeeTableBody.innerHTML = "";

      const employees = data.employee || data.data || [];

      if (recordCount) {
        recordCount.textContent = `${employees.length} Employee${employees.length === 1 ? '' : 's'}`;
      }

      if (data.status === "success" && employees.length > 0) {
        if (emptyState) emptyState.style.display = "none";

        employees.forEach((emp) => {
          const midInitial = emp.middle_initial ? escapeHtml(emp.middle_initial) : "";
          const fullName = `${escapeQuote(emp.first_name)} ${escapeQuote(emp.last_name)}`.trim();
          const row = `
            <tr class="employee-row" data-id="${emp.id}" onclick="viewEmployee(${emp.id})" title="Click row to view details">
              <td class="text-center">${escapeHtml(emp.id)}</td>
              <td><strong>${escapeHtml(emp.first_name)}</strong></td>
              <td class="text-center">${midInitial}</td>
              <td><strong>${escapeHtml(emp.last_name)}</strong></td>
              <td>${escapeHtml(emp.mobile_number)}</td>
              <td>${escapeHtml(emp.email)}</td>
              <td class="text-center">${escapeHtml(emp.sex)}</td>
              <td>${escapeHtml(emp.job_title)}</td>
              <td class="actions" onclick="event.stopPropagation()">
                <button type="button" class="edit-btn" onclick="event.stopPropagation(); openEditModal(${emp.id}, '${escapeQuote(emp.first_name)}', '${escapeQuote(emp.middle_initial || '')}', '${escapeQuote(emp.last_name)}', '${escapeQuote(emp.email)}', '${escapeQuote(emp.mobile_number)}', '${escapeQuote(emp.sex)}', '${escapeQuote(emp.job_title)}')">Edit</button>
                <button type="button" class="delete-btn" onclick="event.stopPropagation(); deletePrompt(${emp.id}, '${fullName}')">Delete</button>
              </td>
            </tr>
          `;
          employeeTableBody.innerHTML += row;
        });
      } else {
        if (emptyState) emptyState.style.display = "block";
      }
    })
    .catch((error) => {
      console.error("Error fetching employees:", error);
      showToast("error", "Network Error", "Could not load employee records from server.");
    });
}

/**
 * Open Delete Confirmation Modal (from GitHub repository)
 */
function deletePrompt(id, name) {
  deleteTargetId = id;
  const nameEl = document.getElementById("deleteEmployeeName");
  if (nameEl) {
    nameEl.textContent = name ? `"${name}" (ID #${id})` : `ID #${id}`;
  }
  const modal = document.getElementById("deleteModal");
  if (modal) {
    modal.style.display = "flex";
  } else {
    // Fallback to browser confirm if modal is not present
    if (confirm("Are you sure you want to delete this employee?")) {
      handleConfirmDelete();
    }
  }
}

function closeDeleteModal() {
  const modal = document.getElementById("deleteModal");
  if (modal) modal.style.display = "none";
  deleteTargetId = null;
}

/**
 * Confirm and Execute Employee Deletion (DELETE ?id=X) (from GitHub repository)
 */
async function handleConfirmDelete() {
  if (!deleteTargetId) return;

  const btn = document.getElementById("confirmDeleteBtn");
  if (btn) {
    btn.disabled = true;
    btn.textContent = "Deleting...";
  }

  const idToDelete = deleteTargetId;

  try {
    const res = await fetch(`${API_URL}?id=${encodeURIComponent(idToDelete)}`, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: idToDelete })
    });

    const result = await res.json();

    if (res.ok && result.status === "success") {
      alert(result.message || "Employee deleted successfully!");
      showToast("success", "Employee Deleted", result.message || "Employee record has been removed.");
      closeDeleteModal();
      await loadEmployees();
    } else {
      alert("Delete Failed: " + (result.message || "Could not delete employee record."));
      showToast("error", "Delete Failed", result.message || "Could not delete employee record.");
    }
  } catch (err) {
    console.warn("DELETE request failed, attempting fallback:", err);
    try {
      const fallbackRes = await fetch(API_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: idToDelete, _method: "DELETE" })
      });
      const result = await fallbackRes.json();
      if (result.status === "success") {
        alert(result.message || "Employee deleted successfully!");
        showToast("success", "Employee Deleted", result.message || "Employee record has been removed.");
        closeDeleteModal();
        await loadEmployees();
      } else {
        alert("Delete Failed: " + (result.message || "Could not delete employee record."));
      }
    } catch (fallbackErr) {
      console.error("Delete failed completely:", fallbackErr);
      alert("Network Error: Could not communicate with server.");
      showToast("error", "Request Failed", "Network communication error.");
    }
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = "Delete Employee";
    }
    deleteTargetId = null;
  }
}

/**
 * Delete Employee function supporting both direct calling and opening deletePrompt
 */
function deleteEmployee(id, employeeName) {
  deletePrompt(id, employeeName);
}

/**
 * View Employee Profile Modal (Triggered by Clicking a Table Row)
 */
function viewEmployee(id) {
  const modal = document.getElementById("viewModal");
  if (!modal) return;

  // Placeholder state while loading
  document.getElementById("viewBadgeId").textContent = `ID #${id}`;
  document.getElementById("viewAvatarBadge").textContent = "...";
  document.getElementById("viewFullName").textContent = "Loading employee details...";
  document.getElementById("viewJobBadge").textContent = "...";
  document.getElementById("viewFirstName").textContent = "...";
  document.getElementById("viewMiddleInitial").textContent = "...";
  document.getElementById("viewLastName").textContent = "...";
  document.getElementById("viewSex").textContent = "...";
  document.getElementById("viewEmail").textContent = "...";
  document.getElementById("viewMobileNumber").textContent = "...";

  modal.style.display = "flex";

  fetch(`${API_URL}?id=${id}&t=${new Date().getTime()}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success" && (data.employee || data.data)) {
        const emp = data.employee || data.data;
        currentViewedEmployee = emp;

        const initials = `${emp.first_name.charAt(0)}${emp.last_name.charAt(0)}`.toUpperCase();
        document.getElementById("viewAvatarBadge").textContent = initials || "EM";
        document.getElementById("viewBadgeId").textContent = `ID #${emp.id}`;
        document.getElementById("viewFullName").textContent = `${emp.first_name} ${emp.middle_initial ? emp.middle_initial + '. ' : ''}${emp.last_name}`;
        document.getElementById("viewJobBadge").textContent = emp.job_title;

        document.getElementById("viewFirstName").textContent = emp.first_name;
        document.getElementById("viewMiddleInitial").textContent = emp.middle_initial || "-";
        document.getElementById("viewLastName").textContent = emp.last_name;
        document.getElementById("viewSex").textContent = emp.sex;

        const emailEl = document.getElementById("viewEmail");
        emailEl.innerHTML = `<a href="mailto:${escapeHtml(emp.email)}" style="color: var(--primary-crimson); text-decoration: underline;">${escapeHtml(emp.email)}</a>`;

        const mobileEl = document.getElementById("viewMobileNumber");
        mobileEl.innerHTML = `<a href="tel:${escapeHtml(emp.mobile_number)}" style="color: var(--primary-crimson); text-decoration: underline;">${escapeHtml(emp.mobile_number)}</a>`;
      } else {
        alert("Could not load employee details: " + (data.message || "Record not found."));
        closeViewModal();
      }
    })
    .catch((error) => {
      console.error("Error viewing employee:", error);
      alert("Network Error: Could not retrieve employee record.");
      closeViewModal();
    });
}

function closeViewModal() {
  const modal = document.getElementById("viewModal");
  if (modal) modal.style.display = "none";
  currentViewedEmployee = null;
}

function editCurrentViewedEmployee() {
  if (!currentViewedEmployee) return;
  const emp = currentViewedEmployee;
  closeViewModal();
  openEditModal(emp.id, emp.first_name, emp.middle_initial || '', emp.last_name, emp.email, emp.mobile_number, emp.sex, emp.job_title);
}

/**
 * Search and Filter Data (PDF 3 Page 13)
 */
function filterEmployees() {
  const searchInput = document.getElementById("searchBox");
  const genderFilter = document.getElementById("genderFilter") || document.getElementById("filterSex");
  const jobTitleFilter = document.getElementById("jobTitleFilter") || document.getElementById("filterJobTitle");

  const query = searchInput ? searchInput.value.toLowerCase().trim() : "";
  const selectedGender = genderFilter ? genderFilter.value : "";
  const selectedJob = jobTitleFilter ? jobTitleFilter.value : "";

  const rows = document.querySelectorAll("#employeeTableBody tr.employee-row");
  let visibleCount = 0;

  rows.forEach((row) => {
    const text = row.innerText.toLowerCase();
    const cells = row.getElementsByTagName("td");

    const rowGender = cells[6] ? cells[6].innerText.trim() : "";
    const rowJob = cells[7] ? cells[7].innerText.trim() : "";

    const matchesSearch = query === "" || text.includes(query);
    const matchesGender = selectedGender === "" || rowGender === selectedGender;
    const matchesJob = selectedJob === "" || rowJob === selectedJob;

    if (matchesSearch && matchesGender && matchesJob) {
      row.style.display = "";
      visibleCount++;
    } else {
      row.style.display = "none";
    }
  });

  const recordCount = document.getElementById("recordCount");
  if (recordCount) {
    recordCount.textContent = `${visibleCount} Employee${visibleCount === 1 ? '' : 's'}`;
  }

  const emptyState = document.getElementById("emptyState");
  if (emptyState) {
    emptyState.style.display = visibleCount === 0 ? "block" : "none";
  }
}

/**
 * Client-Side Validation Helper Functions
 */
function showFieldError(fieldId, message) {
  const input = document.getElementById(fieldId);
  if (!input) return;

  input.classList.add("input-error");

  let errorSpan = input.parentElement.querySelector(".field-error-msg");
  if (!errorSpan) {
    errorSpan = document.createElement("span");
    errorSpan.className = "field-error-msg";
    input.parentElement.appendChild(errorSpan);
  }
  errorSpan.textContent = message;
}

function clearValidationErrors(prefix = "") {
  const fields = prefix === "edit"
    ? ["editFirstName", "editMiddleInitial", "editLastName", "editEmail", "editMobileNumber", "editSex", "editJobTitle"]
    : ["first_name", "middle_initial", "last_name", "email", "mobile_number", "sex", "job_title"];

  fields.forEach((id) => {
    const el = document.getElementById(id);
    if (el) {
      el.classList.remove("input-error");
      const err = el.parentElement.querySelector(".field-error-msg");
      if (err) err.remove();
    }
  });
}

function validateEmployeeData(prefix = "") {
  clearValidationErrors(prefix);
  let isValid = true;
  const errors = [];

  const isEdit = prefix === "edit";
  const firstName = document.getElementById(isEdit ? "editFirstName" : "first_name").value.trim();
  const mi = document.getElementById(isEdit ? "editMiddleInitial" : "middle_initial").value.trim().toUpperCase();
  const lastName = document.getElementById(isEdit ? "editLastName" : "last_name").value.trim();
  const email = document.getElementById(isEdit ? "editEmail" : "email").value.trim();
  const mobile = document.getElementById(isEdit ? "editMobileNumber" : "mobile_number").value.trim();
  const sex = document.getElementById(isEdit ? "editSex" : "sex").value.trim();
  const jobTitle = document.getElementById(isEdit ? "editJobTitle" : "job_title").value.trim();

  // First Name validation
  if (!firstName) {
    showFieldError(isEdit ? "editFirstName" : "first_name", "First name must not be empty.");
    errors.push("First Name must not be empty.");
    isValid = false;
  } else if (firstName.length < 2) {
    showFieldError(isEdit ? "editFirstName" : "first_name", "First name must be at least 2 characters.");
    errors.push("First Name must be at least 2 characters.");
    isValid = false;
  }

  // Middle Initial validation (Strictly 1 character VARCHAR(1))
  if (mi && !/^[A-Z]{1}$/.test(mi)) {
    showFieldError(isEdit ? "editMiddleInitial" : "middle_initial", "M.I. must be 1 letter only (e.g. A).");
    errors.push("Middle Initial must be 1 letter only (e.g. A).");
    isValid = false;
  }

  // Last Name validation
  if (!lastName) {
    showFieldError(isEdit ? "editLastName" : "last_name", "Last name must not be empty.");
    errors.push("Last Name must not be empty.");
    isValid = false;
  } else if (lastName.length < 2) {
    showFieldError(isEdit ? "editLastName" : "last_name", "Last name must be at least 2 characters.");
    errors.push("Last Name must be at least 2 characters.");
    isValid = false;
  }

  // Email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!email) {
    showFieldError(isEdit ? "editEmail" : "email", "Email address must not be empty.");
    errors.push("Email address must not be empty.");
    isValid = false;
  } else if (!emailRegex.test(email)) {
    showFieldError(isEdit ? "editEmail" : "email", "Email must be in a valid format (e.g. name@domain.com).");
    errors.push("Email must be in a valid format (e.g. name@domain.com).");
    isValid = false;
  }

  // Mobile Number validation
  const cleanMobile = mobile.replace(/[^0-9+]/g, '');
  if (!mobile) {
    showFieldError(isEdit ? "editMobileNumber" : "mobile_number", "Mobile number must not be empty.");
    errors.push("Mobile number must not be empty.");
    isValid = false;
  } else if (cleanMobile.length < 10 || cleanMobile.length > 15) {
    showFieldError(isEdit ? "editMobileNumber" : "mobile_number", "Mobile number must be a valid 10-15 digit phone number.");
    errors.push("Mobile number must be a valid 10-15 digit phone number (e.g. 09123456789).");
    isValid = false;
  }

  // Gender validation
  if (!sex || (sex !== "Male" && sex !== "Female")) {
    showFieldError(isEdit ? "editSex" : "sex", "Please select a gender (Male or Female).");
    errors.push("Please select a gender (Male or Female).");
    isValid = false;
  }

  // Job Title validation
  if (!jobTitle || jobTitle === "Select Job Title") {
    showFieldError(isEdit ? "editJobTitle" : "job_title", "Please select a valid job title.");
    errors.push("Please select a valid job title.");
    isValid = false;
  }

  if (!isValid && errors.length > 0) {
    const summary = errors.map((e, idx) => `${idx + 1}. ${e}`).join("\n");
    alert(`Please correct the following errors:\n\n${summary}`);
    showToast("error", "Validation Failed", "Please correct the highlighted form fields.");
    return false;
  }

  return true;
}

/**
 * Add Employee Modal Controls (PDF 3 Page 16)
 */
function openAddEmployeeModal() {
  clearValidationErrors();

  document.getElementById("first_name").value = "";
  document.getElementById("middle_initial").value = "";
  document.getElementById("last_name").value = "";
  document.getElementById("email").value = "";
  document.getElementById("mobile_number").value = "";
  document.getElementById("sex").value = "";
  document.getElementById("job_title").value = "Select Job Title";

  const modal = document.getElementById("addEmployeeModal");
  if (modal) {
    modal.style.display = "flex";
    const first = document.getElementById("first_name");
    if (first) setTimeout(() => first.focus(), 80);
  }
}

function closeAddEmployeeModal() {
  const modal = document.getElementById("addEmployeeModal");
  if (modal) modal.style.display = "none";
  clearValidationErrors();
}

/**
 * Add Employee to Database (POST request) (PDF 3 Page 16)
 */
function addEmployee() {
  if (!validateEmployeeData()) {
    return;
  }

  const firstName = document.getElementById("first_name").value.trim();
  const mi = document.getElementById("middle_initial").value.trim().toUpperCase();
  const lastName = document.getElementById("last_name").value.trim();
  const email = document.getElementById("email").value.trim();
  const mobile = document.getElementById("mobile_number").value.trim();
  const sex = document.getElementById("sex").value.trim();
  const jobTitle = document.getElementById("job_title").value.trim();

  fetch(API_URL, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      first_name: firstName,
      middle_initial: mi,
      last_name: lastName,
      email: email,
      mobile_number: mobile,
      sex: sex,
      job_title: jobTitle
    })
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        alert(data.message || "Employee added successfully!");
        showToast("success", "Employee Added Successfully!", data.message || `Added "${firstName} ${lastName}" to the database.`);
        closeAddEmployeeModal();
        loadEmployees();
      } else {
        alert("Failed to add employee:\n" + (data.message || "Unknown error occurred."));
        showToast("error", "Error Adding Employee", data.message || "Failed to add employee record.");
        if (data.field) {
          showFieldError(data.field, data.message);
        }
      }
    })
    .catch((error) => {
      console.error("Error adding employee:", error);
      alert("Network Error: Failed to communicate with API server.");
      showToast("error", "Network Error", "Failed to communicate with API server.");
    });
}

/**
 * Edit Employee Modal Controls (PDF 3 Page 19)
 */
function openEditModal(id, firstName, middleInitial, lastName, email, mobile, sex, jobTitle) {
  clearValidationErrors("edit");

  document.getElementById("editId").value = id;
  document.getElementById("editFirstName").value = firstName;
  document.getElementById("editMiddleInitial").value = middleInitial !== "null" && middleInitial !== "undefined" ? middleInitial : "";
  document.getElementById("editLastName").value = lastName;
  document.getElementById("editEmail").value = email;
  document.getElementById("editMobileNumber").value = mobile;
  document.getElementById("editSex").value = sex;
  document.getElementById("editJobTitle").value = jobTitle;

  const modal = document.getElementById("editModal");
  if (modal) {
    modal.style.display = "flex";
    const first = document.getElementById("editFirstName");
    if (first) setTimeout(() => first.focus(), 80);
  }
}

function closeModal() {
  const modal = document.getElementById("editModal");
  if (modal) modal.style.display = "none";
  clearValidationErrors("edit");
}

/**
 * Update Employee Details (PUT request) (PDF 3 Page 19)
 */
function updateEmployee() {
  if (!validateEmployeeData("edit")) {
    return;
  }

  const id = document.getElementById("editId").value;
  const firstName = document.getElementById("editFirstName").value.trim();
  const mi = document.getElementById("editMiddleInitial").value.trim().toUpperCase();
  const lastName = document.getElementById("editLastName").value.trim();
  const email = document.getElementById("editEmail").value.trim();
  const mobile = document.getElementById("editMobileNumber").value.trim();
  const sex = document.getElementById("editSex").value.trim();
  const jobTitle = document.getElementById("editJobTitle").value.trim();

  fetch(API_URL, {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      id: id,
      first_name: firstName,
      middle_initial: mi,
      last_name: lastName,
      email: email,
      mobile_number: mobile,
      sex: sex,
      job_title: jobTitle
    })
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        alert(data.message || "Employee updated successfully!");
        showToast("success", "Employee Updated Successfully!", data.message || `Updated details for "${firstName} ${lastName}".`);
        closeModal();
        loadEmployees();
      } else {
        alert("Failed to update employee:\n" + (data.message || "Unknown error occurred."));
        showToast("error", "Update Failed", data.message || "Failed to update employee.");
        if (data.field) {
          const editFieldId = "edit" + data.field.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join('');
          showFieldError(editFieldId, data.message);
        }
      }
    })
    .catch((error) => {
      console.error("Error updating employee:", error);
      alert("Network Error: Failed to update employee details.");
      showToast("error", "Network Error", "Failed to update employee details.");
    });
}

/**
 * Floating Toast Notification Banner
 */
function showToast(type, title, message) {
  let container = document.getElementById("toastContainer");
  if (!container) {
    container = document.createElement("div");
    container.id = "toastContainer";
    container.className = "toast-container";
    document.body.appendChild(container);
  }

  const toast = document.createElement("div");
  toast.className = `toast ${type}`;

  toast.innerHTML = `
    <div class="toast-content">
      <h4>${escapeHtml(title)}</h4>
      <p>${escapeHtml(message)}</p>
    </div>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transform = "translateX(40px)";
    toast.style.transition = "all 0.3s ease";
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

/**
 * Event Listeners & Modal Backdrop Clicks
 */
function setupEventListeners() {
  // Click outside modal content to close
  window.addEventListener("click", (event) => {
    const addModal = document.getElementById("addEmployeeModal");
    const editModal = document.getElementById("editModal");
    const viewModal = document.getElementById("viewModal");
    const deleteModal = document.getElementById("deleteModal");
    if (event.target === addModal) closeAddEmployeeModal();
    if (event.target === editModal) closeModal();
    if (event.target === viewModal) closeViewModal();
    if (event.target === deleteModal) closeDeleteModal();
  });

  // ESC key closes modals
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeAddEmployeeModal();
      closeModal();
      closeViewModal();
      closeDeleteModal();
    }
  });

  // Test DB connection button
  const testBtn = document.getElementById("testConnectionBtn");
  if (testBtn) {
    testBtn.addEventListener("click", runConnectionTest);
  }

  // Clear errors dynamically on input
  document.querySelectorAll("input, select").forEach((el) => {
    el.addEventListener("input", () => {
      el.classList.remove("input-error");
      const err = el.parentElement.querySelector(".field-error-msg");
      if (err) err.remove();
    });
    el.addEventListener("change", () => {
      el.classList.remove("input-error");
      const err = el.parentElement.querySelector(".field-error-msg");
      if (err) err.remove();
    });
  });
}

/**
 * String Escaping Utilities
 */
function escapeHtml(str) {
  if (str === null || str === undefined) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function escapeQuote(str) {
  if (!str) return "";
  return String(str).replace(/'/g, "\\'").replace(/"/g, "&quot;");
}

// Explicit Global Window Bindings for Inline HTML Callbacks
window.deletePrompt = deletePrompt;
window.closeDeleteModal = closeDeleteModal;
window.handleConfirmDelete = handleConfirmDelete;
window.deleteEmployee = deleteEmployee;
window.openEditModal = openEditModal;
window.viewEmployee = viewEmployee;
window.closeViewModal = closeViewModal;
window.editCurrentViewedEmployee = editCurrentViewedEmployee;
window.openAddEmployeeModal = openAddEmployeeModal;
window.closeAddEmployeeModal = closeAddEmployeeModal;
window.closeModal = closeModal;
window.addEmployee = addEmployee;
window.updateEmployee = updateEmployee;
window.filterEmployees = filterEmployees;
window.runConnectionTest = runConnectionTest;
window.loadEmployees = loadEmployees;
