/**
 * ==============================================================================
 * EMPLOYEE MANAGEMENT SYSTEM - VANILLA JAVASCRIPT CONTROLLER
 * Pure Vanilla JS utilizing Fetch API for asynchronous RESTful CRUD operations
 * ==============================================================================
 */

// Application State
const AppState = {
  employees: [],
  filteredEmployees: [],
  selectedEmployeeId: null,
  deleteTargetId: null,
  apiEndpoint: 'employee_api.php',
  connectionEndpoint: 'test_connection.php'
};

// DOM Elements Cache
const DOM = {};

/**
 * Initialize Application on DOMContentLoaded
 */
document.addEventListener('DOMContentLoaded', () => {
  cacheDOMElements();
  bindEvents();
  checkConnection();
  loadEmployees();
});

/**
 * Cache necessary DOM references for performance
 */
function cacheDOMElements() {
  DOM.employeeTableBody = document.getElementById('employeeTableBody');
  DOM.emptyState = document.getElementById('emptyState');
  DOM.tableCard = document.getElementById('tableCard');
  DOM.searchInput = document.getElementById('searchInput');
  DOM.deptFilter = document.getElementById('deptFilter');
  DOM.sortFilter = document.getElementById('sortFilter');
  DOM.refreshBtn = document.getElementById('refreshBtn');
  DOM.tableCounter = document.getElementById('tableCounter');

  // Stats
  DOM.statTotalCount = document.getElementById('statTotalCount');
  DOM.statTotalPayroll = document.getElementById('statTotalPayroll');
  DOM.statAvgSalary = document.getElementById('statAvgSalary');
  DOM.statTotalDepts = document.getElementById('statTotalDepts');

  // Modals
  DOM.addModal = document.getElementById('addModal');
  DOM.editModal = document.getElementById('editModal');
  DOM.viewModal = document.getElementById('viewModal');
  DOM.deleteModal = document.getElementById('deleteModal');

  // Forms
  DOM.addForm = document.getElementById('addEmployeeForm');
  DOM.editForm = document.getElementById('editEmployeeForm');
  DOM.inlineForm = document.getElementById('inlineEmployeeForm');
  DOM.inlineId = document.getElementById('inlineId');
  DOM.inlineName = document.getElementById('inlineName');
  DOM.inlineEmail = document.getElementById('inlineEmail');
  DOM.inlinePosition = document.getElementById('inlinePosition');
  DOM.inlineDepartment = document.getElementById('inlineDepartment');
  DOM.inlineSalary = document.getElementById('inlineSalary');
  DOM.inlineSubmitBtn = document.getElementById('inlineSubmitBtn');
  DOM.inlineCancelBtn = document.getElementById('inlineCancelBtn');
  DOM.formTitle = document.getElementById('formTitle');

  // Buttons & Badges
  DOM.openAddBtn = document.getElementById('openAddBtn');
  DOM.tableAddBtn = document.getElementById('tableAddBtn');
  DOM.testConnectionBtn = document.getElementById('testConnectionBtn');
  DOM.confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
  DOM.connectionPill = document.getElementById('connectionPill');
  DOM.toastContainer = document.getElementById('toastContainer');
}

/**
 * Bind User Interactions & Event Handlers
 */
function bindEvents() {
  // Search & Filter (if present)
  if (DOM.searchInput) {
    let searchTimeout = null;
    DOM.searchInput.addEventListener('input', (e) => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        applyFilters();
      }, 200);
    });
  }

  if (DOM.deptFilter) {
    DOM.deptFilter.addEventListener('change', applyFilters);
  }
  if (DOM.sortFilter) {
    DOM.sortFilter.addEventListener('change', applyFilters);
  }
  if (DOM.refreshBtn) {
    DOM.refreshBtn.addEventListener('click', () => {
      loadEmployees();
      showToast('info', 'Refreshed', 'Employee records reloaded from server.');
    });
  }

  // Modal Open / Close
  if (DOM.openAddBtn) {
    DOM.openAddBtn.addEventListener('click', () => openModal(DOM.addModal));
  }
  if (DOM.tableAddBtn) {
    DOM.tableAddBtn.addEventListener('click', () => openModal(DOM.addModal));
  }
  if (DOM.testConnectionBtn) {
    DOM.testConnectionBtn.addEventListener('click', runConnectionTest);
  }
  if (DOM.confirmDeleteBtn) {
    DOM.confirmDeleteBtn.addEventListener('click', handleConfirmDelete);
  }

  // Close buttons inside modals
  document.querySelectorAll('.modal-close-trigger').forEach((btn) => {
    btn.addEventListener('click', () => {
      closeAllModals();
    });
  });

  // Click outside modal card to dismiss
  document.querySelectorAll('.modal-overlay').forEach((overlay) => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        closeAllModals();
      }
    });
  });

  // Esc key closes any open modal
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeAllModals();
    }
  });

  // Form Submissions
  if (DOM.addForm) {
    DOM.addForm.addEventListener('submit', handleAddEmployee);
  }
  if (DOM.editForm) {
    DOM.editForm.addEventListener('submit', handleEditEmployee);
  }
}

/**
 * Check Database Connection Health
 */
async function checkConnection() {
  try {
    const res = await fetch(AppState.connectionEndpoint);
    const result = await res.json();
    if (result.status === 'success') {
      DOM.connectionPill.innerHTML = `
        <span class="status-dot"></span>
        <span>MySQL Connected (v${result.server_version || '8.4'})</span>
      `;
    } else {
      setConnectionFailed();
    }
  } catch (err) {
    setConnectionFailed();
  }
}

function setConnectionFailed() {
  DOM.connectionPill.innerHTML = `
    <span class="status-dot" style="background:#ef4444;box-shadow:0 0 8px #ef4444;"></span>
    <span style="color:#ef4444;">DB Offline</span>
  `;
}

/**
 * Trigger Interactive Connection Test with Feedback
 */
async function runConnectionTest() {
  showToast('info', 'Testing Connection', 'Pinging MySQL database via PDO...');
  try {
    const res = await fetch(AppState.connectionEndpoint);
    const data = await res.json();

    if (res.ok && data.status === 'success') {
      showToast(
        'success',
        'Connection Successful',
        `Database: ${data.database} | Total Employees: ${data.total_employees} | Engine: MySQL ${data.server_version}`
      );
    } else {
      showToast('error', 'Connection Error', data.message || 'Unable to connect to database.');
    }
  } catch (err) {
    showToast('error', 'Network Error', 'Failed to reach connection endpoint.');
  }
}

/**
 * Fetch All Employees via REST API (GET)
 */
async function loadEmployees() {
  try {
    const res = await fetch(AppState.apiEndpoint);
    const result = await res.json();

    if (res.ok && result.status === 'success') {
      AppState.employees = result.data || [];
      populateDepartmentDropdown();
      applyFilters();
      updateMetrics();
    } else {
      showToast('error', 'Fetch Error', result.message || 'Failed to load employee records.');
    }
  } catch (err) {
    showToast('error', 'Network Error', 'Could not communicate with REST API.');
  }
}

/**
 * Dynamically Populate Department Dropdown
 */
function populateDepartmentDropdown() {
  if (!DOM.deptFilter) return;
  const departments = [...new Set(AppState.employees.map((emp) => emp.department))].filter(Boolean).sort();
  const currentVal = DOM.deptFilter.value;

  DOM.deptFilter.innerHTML = '<option value="All">All Departments</option>';
  departments.forEach((dept) => {
    const option = document.createElement('option');
    option.value = dept;
    option.textContent = dept;
    DOM.deptFilter.appendChild(option);
  });

  if (departments.includes(currentVal)) {
    DOM.deptFilter.value = currentVal;
  }
}

/**
 * Apply Search, Filter, and Sort Client-Side
 */
function applyFilters() {
  if (!DOM.searchInput || !DOM.deptFilter || !DOM.sortFilter) {
    AppState.filteredEmployees = AppState.employees;
    renderTable(AppState.employees);
    return;
  }
  const searchTerm = DOM.searchInput.value.toLowerCase().trim();
  const selectedDept = DOM.deptFilter.value;
  const sortMode = DOM.sortFilter.value;

  let filtered = AppState.employees.filter((emp) => {
    const matchesSearch =
      !searchTerm ||
      emp.name.toLowerCase().includes(searchTerm) ||
      emp.email.toLowerCase().includes(searchTerm) ||
      emp.position.toLowerCase().includes(searchTerm) ||
      emp.department.toLowerCase().includes(searchTerm);

    const matchesDept = selectedDept === 'All' || emp.department === selectedDept;

    return matchesSearch && matchesDept;
  });

  // Sorting
  filtered.sort((a, b) => {
    switch (sortMode) {
      case 'id_desc':
        return Number(b.id) - Number(a.id);
      case 'id_asc':
        return Number(a.id) - Number(b.id);
      case 'name_asc':
        return a.name.localeCompare(b.name);
      case 'name_desc':
        return b.name.localeCompare(a.name);
      case 'salary_desc':
        return Number(b.salary) - Number(a.salary);
      case 'salary_asc':
        return Number(a.salary) - Number(b.salary);
      default:
        return Number(b.id) - Number(a.id);
    }
  });

  AppState.filteredEmployees = filtered;
  renderTable(filtered);
}

/**
 * Render Employee Table Rows
 */
function renderTable(employees) {
  DOM.tableCounter.textContent = `${employees.length} Employee${employees.length === 1 ? '' : 's'}`;

  if (employees.length === 0) {
    DOM.employeeTableBody.innerHTML = '';
    DOM.emptyState.style.display = 'block';
    return;
  }

  DOM.emptyState.style.display = 'none';

  const rowsHtml = employees
    .map((emp) => {
      const initials = getInitials(emp.name);
      const deptClass = getDeptClass(emp.department);
      const formattedSalary = formatCurrency(emp.salary);

      return `
      <tr onclick="handleRowClick(event, ${emp.id})" title="Click row to view profile modal">
        <td class="td-id">#${escapeHtml(emp.id)}</td>
        <td>
          <div class="user-cell">
            <div class="avatar">${initials}</div>
            <div class="user-cell-meta">
              <div class="user-name">${escapeHtml(emp.name)}</div>
              <div class="user-email">${escapeHtml(emp.email)}</div>
            </div>
          </div>
        </td>
        <td><span class="cell-position">${escapeHtml(emp.position)}</span></td>
        <td>
          <span class="badge-dept ${deptClass}">${escapeHtml(emp.department)}</span>
        </td>
        <td>
          <span class="salary-text">${formattedSalary}</span>
        </td>
        <td>
          <div class="actions-cell">
            <button class="btn-action-edit" onclick="editEmployee(${emp.id})">Edit</button>
            <button class="btn-action-delete" onclick="deletePrompt(${emp.id}, '${escapeQuote(emp.name)}')">Delete</button>
          </div>
        </td>
      </tr>
    `;
    })
    .join('');

  DOM.employeeTableBody.innerHTML = rowsHtml;
}

/**
 * Handle Row Click to Open View Profile Modal
 */
function handleRowClick(event, id) {
  if (event.target.closest('.btn-action-edit') || event.target.closest('.btn-action-delete')) {
    return;
  }
  viewEmployee(id);
}

/**
 * Compute and Update Key Metric Cards
 */
function updateMetrics() {
  if (!DOM.statTotalCount) return;
  const total = AppState.employees.length;
  DOM.statTotalCount.textContent = total;

  if (total === 0) {
    DOM.statTotalPayroll.textContent = '$0.00';
    DOM.statAvgSalary.textContent = '$0.00';
    DOM.statTotalDepts.textContent = '0';
    return;
  }

  const payroll = AppState.employees.reduce((acc, curr) => acc + Number(curr.salary || 0), 0);
  const avg = payroll / total;
  const depts = new Set(AppState.employees.map((e) => e.department)).size;

  DOM.statTotalPayroll.textContent = formatCurrency(payroll);
  DOM.statAvgSalary.textContent = formatCurrency(avg);
  DOM.statTotalDepts.textContent = depts;
}

/**
 * Handle Add Employee Form Submission (POST)
 */
async function handleAddEmployee(e) {
  e.preventDefault();

  const formData = {
    name: document.getElementById('addName').value.trim(),
    email: document.getElementById('addEmail').value.trim(),
    position: document.getElementById('addPosition').value.trim(),
    department: document.getElementById('addDepartment').value.trim(),
    salary: parseFloat(document.getElementById('addSalary').value)
  };

  if (!formData.name || !formData.email || !formData.position || !formData.department || isNaN(formData.salary)) {
    showToast('error', 'Validation Error', 'All fields are required and salary must be numeric.');
    return;
  }

  const submitBtn = DOM.addForm.querySelector('button[type="submit"]');
  submitBtn.disabled = true;
  submitBtn.textContent = 'Saving...';

  try {
    const res = await fetch(AppState.apiEndpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    });

    const result = await res.json();

    if (res.status === 201 && result.status === 'success') {
      showToast('success', 'Employee Created', `Successfully added "${formData.name}".`);
      DOM.addForm.reset();
      closeAllModals();
      await loadEmployees();
    } else {
      showToast('error', 'Error Creating Employee', result.message || 'Failed to save record.');
    }
  } catch (err) {
    showToast('error', 'Request Failed', 'Network communication error.');
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Save Employee';
  }
}

/**
 * Fetch and Populate Edit Modal (GET ?id=X)
 */
async function editEmployee(id) {
  try {
    const res = await fetch(`${AppState.apiEndpoint}?id=${id}`);
    const result = await res.json();

    if (res.ok && (result.success || result.status === 'success')) {
      const emp = result.data;
      // Populate and open edit modal popup
      document.getElementById('editId').value = emp.id;
      document.getElementById('editName').value = emp.name;
      document.getElementById('editEmail').value = emp.email;
      document.getElementById('editPosition').value = emp.position;
      document.getElementById('editDepartment').value = emp.department || 'General';
      document.getElementById('editSalary').value = emp.salary;

      openModal(DOM.editModal);
    } else {
      showToast('error', 'Not Found', result.message || 'Employee details could not be found.');
    }
  } catch (err) {
    showToast('error', 'Error', 'Failed to retrieve employee record.');
  }
}

/**
 * Handle Edit Employee Form Submission (PUT)
 */
async function handleEditEmployee(e) {
  e.preventDefault();

  const id = parseInt(document.getElementById('editId').value, 10);
  const formData = {
    id: id,
    name: document.getElementById('editName').value.trim(),
    email: document.getElementById('editEmail').value.trim(),
    position: document.getElementById('editPosition').value.trim(),
    department: document.getElementById('editDepartment').value.trim(),
    salary: parseFloat(document.getElementById('editSalary').value)
  };

  if (!formData.name || !formData.email || !formData.position || !formData.department || isNaN(formData.salary)) {
    showToast('error', 'Validation Error', 'All fields are required and salary must be numeric.');
    return;
  }

  const submitBtn = DOM.editForm.querySelector('button[type="submit"]');
  submitBtn.disabled = true;
  submitBtn.textContent = 'Updating...';

  try {
    const res = await fetch(AppState.apiEndpoint, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    });

    const result = await res.json();

    if (res.ok && result.status === 'success') {
      showToast('success', 'Employee Updated', `Updated details for "${formData.name}".`);
      closeAllModals();
      await loadEmployees();
    } else {
      showToast('error', 'Update Failed', result.message || 'Could not update employee record.');
    }
  } catch (err) {
    showToast('error', 'Request Failed', 'Network communication error.');
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Update Employee';
  }
}

/**
 * Fetch and Display Employee Details in View Modal (GET ?id=X)
 */
async function viewEmployee(id) {
  try {
    const res = await fetch(`${AppState.apiEndpoint}?id=${id}`);
    const result = await res.json();

    if (res.ok && result.status === 'success') {
      const emp = result.data;
      const viewDetailsContainer = document.getElementById('viewDetailsContainer');

      viewDetailsContainer.innerHTML = `
        <div style="text-align:center;margin-bottom:20px;">
          <div class="avatar" style="width:64px;height:64px;font-size:22px;margin:0 auto 12px;">${getInitials(emp.name)}</div>
          <h3 style="font-size:20px;font-weight:700;">${escapeHtml(emp.name)}</h3>
          <p style="color:var(--text-secondary);font-size:14px;">${escapeHtml(emp.position)}</p>
        </div>
        <div class="detail-list">
          <div class="detail-item">
            <span class="detail-label">Employee ID</span>
            <span class="detail-value">#${escapeHtml(emp.id)}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Email Address</span>
            <span class="detail-value">${escapeHtml(emp.email)}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Department</span>
            <span class="detail-value"><span class="badge-dept ${getDeptClass(emp.department)}">${escapeHtml(emp.department)}</span></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Annual Salary</span>
            <span class="detail-value" style="color:var(--success);font-weight:700;">${formatCurrency(emp.salary)}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Date Added</span>
            <span class="detail-value">${escapeHtml(emp.created_at || 'N/A')}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Last Modified</span>
            <span class="detail-value">${escapeHtml(emp.updated_at || 'N/A')}</span>
          </div>
        </div>
      `;

      openModal(DOM.viewModal);
    } else {
      showToast('error', 'Error', result.message || 'Record not found.');
    }
  } catch (err) {
    showToast('error', 'Error', 'Failed to retrieve employee details.');
  }
}

/**
 * Open Delete Confirmation Modal
 */
function deletePrompt(id, name) {
  AppState.deleteTargetId = id;
  document.getElementById('deleteEmployeeName').textContent = name;
  openModal(DOM.deleteModal);
}

/**
 * Confirm and Execute Employee Deletion (DELETE ?id=X)
 */
async function handleConfirmDelete() {
  if (!AppState.deleteTargetId) return;

  const btn = document.getElementById('confirmDeleteBtn');
  btn.disabled = true;
  btn.textContent = 'Deleting...';

  try {
    const res = await fetch(`${AppState.apiEndpoint}?id=${AppState.deleteTargetId}`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' }
    });

    const result = await res.json();

    if (res.ok && result.status === 'success') {
      showToast('success', 'Employee Deleted', result.message || 'Employee record has been removed.');
      closeAllModals();
      await loadEmployees();
    } else {
      showToast('error', 'Delete Failed', result.message || 'Could not delete employee record.');
    }
  } catch (err) {
    showToast('error', 'Request Failed', 'Network communication error.');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Delete Employee';
    AppState.deleteTargetId = null;
  }
}

/**
 * Modal Visibility Helpers
 */
function openModal(modalEl) {
  closeAllModals();
  modalEl.classList.add('active');
  const firstInput = modalEl.querySelector('input:not([type="hidden"]), select');
  if (firstInput) {
    setTimeout(() => firstInput.focus(), 100);
  }
}

function closeAllModals() {
  document.querySelectorAll('.modal-overlay').forEach((modal) => {
    modal.classList.remove('active');
  });
}

/**
 * Toast Notification Banner
 */
function showToast(type, title, message) {
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;

  const iconSvg = {
    success: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`,
    error: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`,
    info: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`
  }[type] || '';

  toast.innerHTML = `
    <div>${iconSvg}</div>
    <div class="toast-content">
      <h4>${escapeHtml(title)}</h4>
      <p>${escapeHtml(message)}</p>
    </div>
  `;

  DOM.toastContainer.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(50px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

/**
 * Utility Functions
 */
function formatCurrency(amount) {
  const num = parseFloat(amount) || 0;
  return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function getInitials(name) {
  if (!name) return 'EM';
  const parts = name.trim().split(/\s+/);
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }
  return parts[0].substring(0, 2).toUpperCase();
}

function getDeptClass(dept) {
  if (!dept) return 'dept-default';
  const clean = dept.toLowerCase().trim();
  if (clean.includes('engineer') || clean.includes('dev')) return 'dept-engineering';
  if (clean.includes('product')) return 'dept-product';
  if (clean.includes('human') || clean.includes('hr')) return 'dept-hr';
  if (clean.includes('finance') || clean.includes('account')) return 'dept-finance';
  if (clean.includes('market')) return 'dept-marketing';
  if (clean.includes('design') || clean.includes('ui') || clean.includes('ux')) return 'dept-design';
  if (clean.includes('operat')) return 'dept-operations';
  return 'dept-default';
}

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function escapeQuote(str) {
  if (!str) return '';
  return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

/**
 * Handle Inline Employee Form Submission (Instructor Layout)
 */
async function handleInlineFormSubmit(e) {
  e.preventDefault();

  const id = DOM.inlineId.value.trim();
  const formData = {
    name: DOM.inlineName.value.trim(),
    email: DOM.inlineEmail.value.trim(),
    position: DOM.inlinePosition.value.trim(),
    department: DOM.inlineDepartment ? DOM.inlineDepartment.value : 'General',
    salary: parseFloat(DOM.inlineSalary.value)
  };

  if (!formData.name || !formData.email || !formData.position || isNaN(formData.salary)) {
    showToast('error', 'Validation Error', 'Please provide Name, Email, Position, and a numeric Salary.');
    return;
  }

  DOM.inlineSubmitBtn.disabled = true;

  try {
    if (id) {
      // PUT Request (Update)
      formData.id = parseInt(id, 10);
      const res = await fetch(AppState.apiEndpoint, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
      });
      const result = await res.json();
      if (res.ok && (result.success || result.status === 'success')) {
        showToast('success', 'Employee Updated', `Updated "${formData.name}" successfully.`);
        resetInlineForm();
        await loadEmployees();
      } else {
        showToast('error', 'Update Error', result.message || 'Failed to update employee.');
      }
    } else {
      // POST Request (Add)
      const res = await fetch(AppState.apiEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
      });
      const result = await res.json();
      if (res.ok && (result.success || result.status === 'success')) {
        showToast('success', 'Employee Added', `Added "${formData.name}" successfully.`);
        resetInlineForm();
        await loadEmployees();
      } else {
        showToast('error', 'Create Error', result.message || 'Failed to add employee.');
      }
    }
  } catch (err) {
    showToast('error', 'Network Error', 'Failed to communicate with REST API.');
  } finally {
    DOM.inlineSubmitBtn.disabled = false;
  }
}

/**
 * Reset Inline Form back to Add Mode
 */
function resetInlineForm() {
  if (!DOM.inlineId) return;
  DOM.inlineId.value = '';
  DOM.inlineForm.reset();
  if (DOM.inlineDepartment) DOM.inlineDepartment.value = '';
  const modeBadge = document.getElementById('formModeBadge');
  if (modeBadge) modeBadge.style.display = 'none';
  DOM.formTitle.textContent = 'Add New Employee';
  DOM.inlineSubmitBtn.innerHTML = `
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
    Add Employee
  `;
  DOM.inlineCancelBtn.style.display = 'none';
}

