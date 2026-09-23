<?php
/**
 * Employee Management System - Main GUI (HTML + PHP)
 * Western Mindanao State University - College of Computing Studies
 * Reference: 03-Simple-Web-Application-Development-with-API-implementation.pdf (Pages 6, 11, 12, 15, 18)
 */

$dbPath = file_exists('../api/database.php') ? '../api/database.php' : (file_exists('api/database.php') ? 'api/database.php' : 'database.php');
$testPath = file_exists('../class/DbTest.php') ? '../class/DbTest.php' : (file_exists('class/DbTest.php') ? 'class/DbTest.php' : 'DbTest.php');

require_once $dbPath;
require_once $testPath;

$database = new Database();
$conn = $database->getConnection();

$test = new DbTest($conn);
$connectionStatus = $test->checkConnection();

$cssPath = file_exists('../style/style.css') ? '../style/style.css' : (file_exists('style/style.css') ? 'style/style.css' : 'style.css');
$jsFuncPath = file_exists('../javascript/functions.js') ? '../javascript/functions.js' : (file_exists('javascript/functions.js') ? 'javascript/functions.js' : 'functions.js');
$empJsPath = file_exists('employee.js') ? 'employee.js' : 'employees/employee.js';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Management Information System</title>
  <link rel="stylesheet" href="<?= $cssPath ?>?v=<?= time() ?>">
  <style>
    <?php
    $rawCssPath = dirname(__DIR__) . '/style/style.css';
    if (!file_exists($rawCssPath)) $rawCssPath = __DIR__ . '/../style/style.css';
    if (!file_exists($rawCssPath)) $rawCssPath = __DIR__ . '/style/style.css';
    if (!file_exists($rawCssPath)) $rawCssPath = __DIR__ . '/style.css';
    if (file_exists($rawCssPath)) include $rawCssPath;
    ?>
  </style>
  <script src="<?= $jsFuncPath ?>"></script>
  <script src="<?= $empJsPath ?>?v=<?= time() ?>"></script>
</head>
<body>

  <!-- Floating Toast Notifications -->
  <div id="toastContainer" class="toast-container" aria-live="polite"></div>

  <!-- Header (PDF 3 Page 11) -->
  <div class="header">
    <div class="navbar">
      <button id="menu-toggle" class="menu-toggle" aria-label="Toggle navigation menu">&#9776;</button>
      <div class="logo">Management Information System</div>
      <ul class="menu">
        <li><a href="#">Departments</a></li>
        <li><a href="../employees/" class="active">Employees</a></li>
        <li><a href="#">Products</a></li>
        <li><a href="#">Orders</a></li>
      </ul>
    </div>
  </div>

  <!-- Status Container Below the Header (PDF 3 Page 11) -->
  <div class="status-container">
    Database Connection Status:
    <span id="connectionStatus" class="status <?= $connectionStatus['status'] === 'success' ? 'success' : 'error' ?>">
      <?= htmlspecialchars($connectionStatus['message']) ?>
    </span>
    <button id="testConnectionBtn" class="btn-test-db" onclick="runConnectionTest()" title="Ping MySQL via test_connection.php">Test DB</button>
  </div>

  <!-- Employee Table (PDF 3 Page 12) -->
  <div class="container">
    <div class="table-container">
      <div class="page-header">
        <div class="page-title">
          Employee List
          <span id="recordCount" class="record-badge">0 Employees</span>
        </div>
        <button class="add-btn" onclick="openAddEmployeeModal()">+Add Employee</button>
      </div>

      <!-- Search and Filter Section (PDF 3 Page 12) -->
      <div class="search-filter">
        <input type="text" id="searchBox" placeholder="Search by Name..." onkeyup="filterEmployees()">
        
        <div class="filter-controls">
          <!-- Gender Filter -->
          <select id="filterSex" onchange="filterEmployees()">
            <option value="">Filter by Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>

          <!-- Job Title Filter -->
          <select id="filterJobTitle" onchange="filterEmployees()">
            <option value="">Select Job Title</option>
            <option value="Project Manager">Project Manager</option>
            <option value="Business Analyst">Business Analyst</option>
            <option value="Fullstack Software Engineer">Fullstack Software Engineer</option>
            <option value="Front End Developer">Front End Developer</option>
            <option value="Back End Developer">Back End Developer</option>
            <option value="Quality Assurance Engineer">Quality Assurance Engineer</option>
          </select>
        </div>
      </div>

      <!-- Data Table with Crimson Header (PDF 3 Page 12) -->
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th class="text-center" style="width: 60px;">ID</th>
              <th>First Name</th>
              <th class="text-center" style="width: 50px;">M.I.</th>
              <th>Last Name</th>
              <th>Mobile</th>
              <th>Email</th>
              <th class="text-center" style="width: 80px;">Sex</th>
              <th>Job Title</th>
              <th class="text-center" style="width: 150px;">Actions</th>
            </tr>
          </thead>
          <tbody id="employeeTableBody">
            <!-- Dynamic rows will be loaded by employee.js -->
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div id="emptyState" class="empty-state" style="display: none;">
        <h3>No Employees Found</h3>
        <p>No records in the database. Click "+Add Employee" to create your first record.</p>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="app-footer">
    <p>Republic of the Philippines &bull; Western Mindanao State University &bull; College of Computing Studies &bull; ADS133</p>
  </footer>

  <!-- =========================================================================
       MODALS
       ========================================================================= -->

  <!-- Add Employee Modal (PDF 3 Page 15) -->
  <div id="addEmployeeModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="addModalTitle">
    <div class="modal-content">
      <span class="close" onclick="closeAddEmployeeModal()">&times;</span>
      <h3 id="addModalTitle">Add New Employee</h3>
      <div class="form-grid">
        <!-- First Column -->
        <div class="form-group">
          <div class="field-wrap">
            <label for="first_name">First Name *</label>
            <input type="text" id="first_name" placeholder="First Name" autocomplete="off">
          </div>
          <div class="field-wrap">
            <label for="middle_initial">M.I.</label>
            <input type="text" id="middle_initial" placeholder="M.I." maxlength="1" oninput="this.value = this.value.toUpperCase()">
          </div>
          <div class="field-wrap">
            <label for="last_name">Last Name *</label>
            <input type="text" id="last_name" placeholder="Last Name" autocomplete="off">
          </div>
          <div class="field-wrap">
            <label for="mobile_number">Mobile Number *</label>
            <input type="text" id="mobile_number" placeholder="Mobile Number" autocomplete="off">
          </div>
        </div>

        <!-- Second Column -->
        <div class="form-group">
          <div class="field-wrap">
            <label for="email">Email *</label>
            <input type="email" id="email" placeholder="Email" autocomplete="off">
          </div>
          <div class="field-wrap">
            <label for="sex">Gender Selection *</label>
            <select id="sex">
              <option value="">Select Gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
          <div class="field-wrap">
            <label for="job_title">Job Title Selection *</label>
            <select id="job_title">
              <option value="Select Job Title">Select Job Title</option>
              <option value="Project Manager">Project Manager</option>
              <option value="Business Analyst">Business Analyst</option>
              <option value="Fullstack Software Engineer">Fullstack Software Engineer</option>
              <option value="Front End Developer">Front End Developer</option>
              <option value="Back End Developer">Back End Developer</option>
              <option value="Quality Assurance Engineer">Quality Assurance Engineer</option>
            </select>
          </div>
        </div>
      </div>
      <button class="modal-submit-btn" onclick="addEmployee()">Save Employee Details</button>
    </div>
  </div>

  <!-- Edit Employee Modal (PDF 3 Page 18) -->
  <div id="editModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h3 id="editModalTitle">Update Employee</h3>
      <div class="form-grid">
        <!-- First Column -->
        <div class="form-group">
          <input type="hidden" id="editId">
          <div class="field-wrap">
            <label for="editFirstName">First Name *</label>
            <input type="text" id="editFirstName" placeholder="First Name" autocomplete="off">
          </div>
          <div class="field-wrap">
            <label for="editMiddleInitial">M.I.</label>
            <input type="text" id="editMiddleInitial" placeholder="M.I." maxlength="1" oninput="this.value = this.value.toUpperCase()">
          </div>
          <div class="field-wrap">
            <label for="editLastName">Last Name *</label>
            <input type="text" id="editLastName" placeholder="Last Name" autocomplete="off">
          </div>
          <div class="field-wrap">
            <label for="editMobileNumber">Mobile Number *</label>
            <input type="text" id="editMobileNumber" placeholder="Mobile Number" autocomplete="off">
          </div>
        </div>

        <!-- Second Column -->
        <div class="form-group">
          <div class="field-wrap">
            <label for="editEmail">Email *</label>
            <input type="email" id="editEmail" placeholder="Email" autocomplete="off">
          </div>
          <div class="field-wrap">
            <label for="editSex">Gender *</label>
            <select id="editSex">
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
          <div class="field-wrap">
            <label for="editJobTitle">Job Title *</label>
            <select id="editJobTitle">
              <option value="Select Job Title">Select Job Title</option>
              <option value="Project Manager">Project Manager</option>
              <option value="Business Analyst">Business Analyst</option>
              <option value="Fullstack Software Engineer">Fullstack Software Engineer</option>
              <option value="Front End Developer">Front End Developer</option>
              <option value="Back End Developer">Back End Developer</option>
              <option value="Quality Assurance Engineer">Quality Assurance Engineer</option>
            </select>
          </div>
        </div>
      </div>
      <button class="modal-submit-btn" onclick="updateEmployee()">Save Changes</button>
    </div>
  </div>

  <!-- View Employee Details Modal (Triggered by Clicking Any Table Row) -->
  <div id="viewModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
    <div class="modal-content view-modal-content">
      <span class="close" onclick="closeViewModal()">&times;</span>
      
      <div class="view-modal-header">
        <div class="view-avatar-badge" id="viewAvatarBadge">EM</div>
        <div class="view-header-info">
          <h3 id="viewModalTitle">Employee Profile</h3>
          <span class="view-id-badge" id="viewBadgeId">ID #0</span>
        </div>
      </div>

      <div class="view-hero-card">
        <div class="view-hero-name" id="viewFullName">--</div>
        <div class="view-hero-role" id="viewJobBadge">--</div>
      </div>

      <div class="view-details-grid">
        <div class="view-detail-card">
          <span class="view-detail-label">First Name</span>
          <span class="view-detail-value" id="viewFirstName">--</span>
        </div>
        <div class="view-detail-card">
          <span class="view-detail-label">Middle Initial</span>
          <span class="view-detail-value" id="viewMiddleInitial">--</span>
        </div>
        <div class="view-detail-card">
          <span class="view-detail-label">Last Name</span>
          <span class="view-detail-value" id="viewLastName">--</span>
        </div>
        <div class="view-detail-card">
          <span class="view-detail-label">Gender</span>
          <span class="view-detail-value" id="viewSex">--</span>
        </div>
        <div class="view-detail-card full-span">
          <span class="view-detail-label">Email Address</span>
          <span class="view-detail-value" id="viewEmail">--</span>
        </div>
        <div class="view-detail-card full-span">
          <span class="view-detail-label">Mobile Number</span>
          <span class="view-detail-value" id="viewMobileNumber">--</span>
        </div>
      </div>

      <div class="view-modal-actions">
        <button type="button" class="btn-edit-from-view" onclick="editCurrentViewedEmployee()">Edit Employee</button>
        <button type="button" class="btn-close-view" onclick="closeViewModal()">Close</button>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal (from GitHub repository) -->
  <div id="deleteModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
    <div class="modal-content" style="max-width: 440px;">
      <span class="close" onclick="closeDeleteModal()">&times;</span>
      <h3 id="deleteModalTitle" style="color: var(--danger-red, #b02a37);">Confirm Deletion</h3>
      <div style="padding: 10px 0 20px;">
        <p style="margin-bottom: 12px; font-size: 15px; color: #231f20;">Are you sure you want to delete employee <strong id="deleteEmployeeName">this employee</strong>?</p>
        <p style="font-size: 13px; color: var(--text-muted, #6c6364); margin: 0;">This action will execute a <code>DELETE</code> query against the MySQL database and cannot be undone.</p>
      </div>
      <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid var(--border-light);">
        <button type="button" class="btn-close-view" style="padding: 8px 16px;" onclick="closeDeleteModal()">Cancel</button>
        <button type="button" id="confirmDeleteBtn" class="delete-btn" style="padding: 8px 18px; font-size: 13.5px;" onclick="handleConfirmDelete()">Delete Employee</button>
      </div>
    </div>
  </div>

  <!-- JavaScript for API Calls (PDF 3 Page 10) -->
  <script src="<?= $empJsPath ?>?v=<?= time() ?>"></script>
</body>
</html>
