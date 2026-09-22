<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Employee Management System built with Core PHP, MySQL, REST API, Pure CSS, and Vanilla JavaScript Fetch API.">
  <meta name="author" content="ADS133 Activity">
  <title>Employee Management System | ADS133 REST API</title>
  
  <!-- Modern Clean Font: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Floating Toast Notifications -->
  <div id="toastContainer" class="toast-container" aria-live="polite"></div>

  <div class="app-container">
    <!-- Top Navigation Bar -->
    <nav class="navbar">
      <div class="brand-container">
        <div class="brand-logo-mark" aria-hidden="true">EM</div>
        <div class="brand-info">
          <span class="brand-title">Employee<span class="brand-accent">Studio</span></span>
          <span class="brand-badge">ADS133</span>
        </div>
      </div>
      <div class="nav-actions">
        <div id="connectionPill" class="status-pill" title="Current MySQL Connection Status">
          <span class="status-dot"></span>
          <span>Checking DB...</span>
        </div>
        <button id="testConnectionBtn" class="btn btn-secondary btn-sm" title="Ping MySQL via test_connection.php">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
          Test DB
        </button>
      </div>
    </nav>

    <!-- Editorial Hero Section (Inspired by Human Figma Design) -->
    <section class="hero-section">
      <div class="hero-kicker">Core PHP &bull; MySQL &bull; REST API &bull; Fetch API</div>
      <h1 class="hero-title">
        Employee Record <mark class="highlight-lime">Management System</mark>
      </h1>
      <p class="hero-description">
        Manage company personnel records with direct PDO database transactions, RESTful endpoints, and asynchronous Fetch requests.
      </p>
    </section>

    <!-- Main Data Table Container -->
    <main class="table-card" id="tableCard">
      <div class="table-header-bar">
        <div class="table-title">
          <h2>Employee Directory</h2>
          <span id="tableCounter" class="badge-counter">0 Employees</span>
        </div>
        <button id="tableAddBtn" class="btn btn-primary btn-sm">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          Add Employee
        </button>
      </div>

      <div class="table-responsive">
        <table class="data-table" id="employeeTable">
          <thead>
            <tr>
              <th style="width: 70px;">ID</th>
              <th>Employee Details</th>
              <th>Position / Role</th>
              <th>Department</th>
              <th>Salary (USD)</th>
              <th style="width: 150px; text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody id="employeeTableBody">
            <!-- Dynamic Rows Injected by script.js -->
          </tbody>
        </table>
      </div>

      <!-- Empty State Graphic -->
      <div id="emptyState" class="empty-state" style="display: none;">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <h3>No Employees Found</h3>
        <p>No records in the database. Click "Add Employee" to create your first record.</p>
      </div>
    </main>

    <!-- Footer -->
    <footer class="app-footer">
      <p>ADS133 Application Development &bull; PHP MySQL Database Connection &bull; RESTful API &bull; Fetch API</p>
    </footer>
  </div>

  <!-- =========================================================================
       MODALS
       ========================================================================= -->

  <!-- 1. Add Employee Modal -->
  <div id="addModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="addModalTitle">
    <div class="modal-card">
      <div class="modal-header">
        <h3 id="addModalTitle">Add New Employee</h3>
        <button class="modal-close-btn modal-close-trigger" aria-label="Close modal">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
      <form id="addEmployeeForm">
        <div class="modal-body">
          <div class="form-group">
            <label for="addName">Full Name *</label>
            <input type="text" id="addName" required placeholder="e.g. Brandon Stark" autocomplete="off">
          </div>
          <div class="form-group">
            <label for="addEmail">Email Address *</label>
            <input type="email" id="addEmail" required placeholder="e.g. b.stark@enterprise.com" autocomplete="off">
          </div>
          <div class="form-group">
            <label for="addPosition">Job Title / Position *</label>
            <input type="text" id="addPosition" required placeholder="e.g. Full Stack Engineer" autocomplete="off">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="addDepartment">Department *</label>
              <select id="addDepartment" required>
                <option value="" disabled selected>Select Department</option>
                <option value="Engineering">Engineering</option>
                <option value="Product">Product</option>
                <option value="Human Resources">Human Resources</option>
                <option value="Finance">Finance</option>
                <option value="Marketing">Marketing</option>
                <option value="Design">Design</option>
                <option value="Operations">Operations</option>
              </select>
            </div>
            <div class="form-group">
              <label for="addSalary">Annual Salary ($) *</label>
              <input type="number" id="addSalary" required step="0.01" min="0" placeholder="e.g. 85000">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary modal-close-trigger">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Employee</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 2. Edit Employee Modal -->
  <div id="editModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
    <div class="modal-card">
      <div class="modal-header">
        <h3 id="editModalTitle">Edit Employee Record</h3>
        <button class="modal-close-btn modal-close-trigger" aria-label="Close modal">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
      <form id="editEmployeeForm">
        <input type="hidden" id="editId">
        <div class="modal-body">
          <div class="form-group">
            <label for="editName">Full Name *</label>
            <input type="text" id="editName" required autocomplete="off">
          </div>
          <div class="form-group">
            <label for="editEmail">Email Address *</label>
            <input type="email" id="editEmail" required autocomplete="off">
          </div>
          <div class="form-group">
            <label for="editPosition">Job Title / Position *</label>
            <input type="text" id="editPosition" required autocomplete="off">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="editDepartment">Department *</label>
              <select id="editDepartment" required>
                <option value="Engineering">Engineering</option>
                <option value="Product">Product</option>
                <option value="Human Resources">Human Resources</option>
                <option value="Finance">Finance</option>
                <option value="Marketing">Marketing</option>
                <option value="Design">Design</option>
                <option value="Operations">Operations</option>
              </select>
            </div>
            <div class="form-group">
              <label for="editSalary">Annual Salary ($) *</label>
              <input type="number" id="editSalary" required step="0.01" min="0">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary modal-close-trigger">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Employee</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 3. View Employee Modal -->
  <div id="viewModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
    <div class="modal-card">
      <div class="modal-header">
        <h3 id="viewModalTitle">Employee Profile</h3>
        <button class="modal-close-btn modal-close-trigger" aria-label="Close modal">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
      <div class="modal-body" id="viewDetailsContainer">
        <!-- Injected by app.js -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary modal-close-trigger">Close</button>
      </div>
    </div>
  </div>

  <!-- 4. Delete Confirmation Modal -->
  <div id="deleteModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
    <div class="modal-card" style="max-width: 440px;">
      <div class="modal-header">
        <h3 id="deleteModalTitle" style="color: var(--danger);">Confirm Deletion</h3>
        <button class="modal-close-btn modal-close-trigger" aria-label="Close modal">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
      <div class="modal-body">
        <p style="margin-bottom: 12px;">Are you sure you want to delete employee <strong id="deleteEmployeeName">--</strong>?</p>
        <p style="font-size: 13px; color: var(--text-muted);">This action will execute a <code>DELETE</code> query against the MySQL database and cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary modal-close-trigger">Cancel</button>
        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete Employee</button>
      </div>
    </div>
  </div>

  <!-- Core JavaScript -->
  <script src="script.js"></script>
</body>
</html>
