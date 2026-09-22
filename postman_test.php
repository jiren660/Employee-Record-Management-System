<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Postman API Test Runner | Employee Management System</title>
  <style>
    :root {
      --pm-bg: #1e1e1e;
      --pm-sidebar: #252526;
      --pm-card: #2d2d2d;
      --pm-border: #3e3e42;
      --pm-text: #cccccc;
      --pm-text-bright: #ffffff;
      --pm-orange: #ff6c37;
      --pm-orange-hover: #e05b2b;
      --pm-green: #0cbb52;
      --pm-blue: #097bed;
      --pm-yellow: #e5a00d;
      --pm-red: #eb2013;
      --pm-font: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--pm-bg);
      color: var(--pm-text);
      font-family: var(--pm-font);
      font-size: 13px;
      line-height: 1.4;
      padding: 24px;
    }
    .container { max-width: 1100px; margin: 0 auto; }
    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid var(--pm-border);
      padding-bottom: 16px;
      margin-bottom: 24px;
    }
    .brand { display: flex; align-items: center; gap: 12px; }
    .logo-pm {
      width: 36px; height: 36px; background: var(--pm-orange);
      border-radius: 50%; display: flex; align-items: center; justify-content: center;
      color: white; font-weight: 800; font-size: 16px;
    }
    h1 { font-size: 20px; color: var(--pm-text-bright); }
    .status-bar { display: flex; gap: 10px; }
    .btn-run-all {
      background: var(--pm-orange); color: white; border: none;
      padding: 8px 18px; border-radius: 4px; font-weight: 600; cursor: pointer;
      display: inline-flex; align-items: center; gap: 6px; font-size: 13px;
    }
    .btn-run-all:hover { background: var(--pm-orange-hover); }
    
    .test-card {
      background: var(--pm-card);
      border: 1px solid var(--pm-border);
      border-radius: 6px;
      margin-bottom: 20px;
      overflow: hidden;
    }
    .test-header {
      background: var(--pm-sidebar);
      padding: 12px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid var(--pm-border);
    }
    .req-meta { display: flex; align-items: center; gap: 12px; }
    .method-pill {
      font-weight: 700; font-size: 12px; padding: 3px 8px; border-radius: 3px;
    }
    .method-get { color: var(--pm-green); border: 1px solid var(--pm-green); background: rgba(12, 187, 82, 0.1); }
    .method-post { color: var(--pm-yellow); border: 1px solid var(--pm-yellow); background: rgba(229, 160, 13, 0.1); }
    .method-put { color: var(--pm-blue); border: 1px solid var(--pm-blue); background: rgba(9, 123, 237, 0.1); }
    .method-delete { color: var(--pm-red); border: 1px solid var(--pm-red); background: rgba(235, 32, 19, 0.1); }
    .req-url { font-family: monospace; color: var(--pm-text-bright); font-size: 13px; }
    .btn-send {
      background: var(--pm-blue); color: white; border: none; padding: 6px 14px;
      border-radius: 4px; font-weight: 600; cursor: pointer; font-size: 12px;
    }
    .btn-send:hover { opacity: 0.9; }

    .test-body {
      display: grid; grid-template-columns: 1fr 1.2fr; gap: 16px; padding: 16px;
    }
    .pane-title {
      font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;
      color: #888; margin-bottom: 8px; font-weight: 600;
    }
    pre {
      background: #181818;
      border: 1px solid var(--pm-border);
      border-radius: 4px;
      padding: 12px;
      color: #9cdcfe;
      font-family: Consolas, "Courier New", monospace;
      font-size: 12px;
      overflow-x: auto;
      max-height: 240px;
    }
    .response-status {
      display: inline-block; font-weight: 700; margin-bottom: 8px; font-size: 12px;
    }
    .status-200, .status-201 { color: var(--pm-green); }
    .status-404, .status-500 { color: var(--pm-red); }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <div class="brand">
        <div class="logo-pm">PM</div>
        <div>
          <h1>Postman API Verification Suite</h1>
          <p style="color:#888;">Employee Management System &bull; <code>http://localhost/employee_management/employee_api.php</code></p>
        </div>
      </div>
      <div class="status-bar">
        <button class="btn-run-all" onclick="runAllTests()">▶ Run All 5 Tests</button>
      </div>
    </header>

    <!-- Test 0: Test Connection -->
    <div class="test-card" id="cardTest0">
      <div class="test-header">
        <div class="req-meta">
          <span class="method-pill method-get">GET</span>
          <span class="req-url">http://localhost/employee_management/test_connection.php</span>
        </div>
        <button class="btn-send" onclick="testConnection()">Send Request</button>
      </div>
      <div class="test-body">
        <div>
          <div class="pane-title">Headers</div>
          <pre>Content-Type: application/json</pre>
        </div>
        <div>
          <div class="pane-title">Response JSON <span id="status0" class="response-status"></span></div>
          <pre id="resp0">// Click 'Send Request' to test database connection</pre>
        </div>
      </div>
    </div>

    <!-- Test 1: GET All Employees -->
    <div class="test-card" id="cardTest1">
      <div class="test-header">
        <div class="req-meta">
          <span class="method-pill method-get">GET</span>
          <span class="req-url">http://localhost/employee_management/employee_api.php</span>
        </div>
        <button class="btn-send" onclick="testGetAll()">Send Request</button>
      </div>
      <div class="test-body">
        <div>
          <div class="pane-title">Request Info</div>
          <pre>Params: none&#10;Headers: Accept: application/json</pre>
        </div>
        <div>
          <div class="pane-title">Response JSON <span id="status1" class="response-status"></span></div>
          <pre id="resp1">// Click 'Send Request' to retrieve all employees</pre>
        </div>
      </div>
    </div>

    <!-- Test 2: GET Employee by ID -->
    <div class="test-card" id="cardTest2">
      <div class="test-header">
        <div class="req-meta">
          <span class="method-pill method-get">GET</span>
          <span class="req-url">http://localhost/employee_management/employee_api.php?id=1</span>
        </div>
        <button class="btn-send" onclick="testGetById()">Send Request</button>
      </div>
      <div class="test-body">
        <div>
          <div class="pane-title">Query Parameters</div>
          <pre>id: 1</pre>
        </div>
        <div>
          <div class="pane-title">Response JSON <span id="status2" class="response-status"></span></div>
          <pre id="resp2">// Click 'Send Request' to retrieve employee #1</pre>
        </div>
      </div>
    </div>

    <!-- Test 3: POST Add Employee -->
    <div class="test-card" id="cardTest3">
      <div class="test-header">
        <div class="req-meta">
          <span class="method-pill method-post">POST</span>
          <span class="req-url">http://localhost/employee_management/employee_api.php</span>
        </div>
        <button class="btn-send" onclick="testPost()">Send Request</button>
      </div>
      <div class="test-body">
        <div>
          <div class="pane-title">Request Body (raw JSON)</div>
          <pre>{
  "name": "Juan Dela Cruz",
  "email": "juan.dc@example.com",
  "position": "Software Developer",
  "salary": 35000
}</pre>
        </div>
        <div>
          <div class="pane-title">Response JSON <span id="status3" class="response-status"></span></div>
          <pre id="resp3">// Click 'Send Request' to add new employee</pre>
        </div>
      </div>
    </div>

    <!-- Test 4: PUT Update Employee -->
    <div class="test-card" id="cardTest4">
      <div class="test-header">
        <div class="req-meta">
          <span class="method-pill method-put">PUT</span>
          <span class="req-url">http://localhost/employee_management/employee_api.php</span>
        </div>
        <button class="btn-send" onclick="testPut()">Send Request</button>
      </div>
      <div class="test-body">
        <div>
          <div class="pane-title">Request Body (raw JSON)</div>
          <pre>{
  "id": 1,
  "name": "Juan Dela Cruz",
  "email": "juan.updated@example.com",
  "position": "Senior Developer",
  "salary": 45000
}</pre>
        </div>
        <div>
          <div class="pane-title">Response JSON <span id="status4" class="response-status"></span></div>
          <pre id="resp4">// Click 'Send Request' to update employee</pre>
        </div>
      </div>
    </div>

    <!-- Test 5: DELETE Employee -->
    <div class="test-card" id="cardTest5">
      <div class="test-header">
        <div class="req-meta">
          <span class="method-pill method-delete">DELETE</span>
          <span class="req-url" id="deleteUrl">http://localhost/employee_management/employee_api.php?id=...</span>
        </div>
        <button class="btn-send" onclick="testDelete()">Send Request</button>
      </div>
      <div class="test-body">
        <div>
          <div class="pane-title">Target Parameter</div>
          <pre id="deleteTargetPre">id: (Auto-targeted newly created ID)</pre>
        </div>
        <div>
          <div class="pane-title">Response JSON <span id="status5" class="response-status"></span></div>
          <pre id="resp5">// Click 'Send Request' to delete employee</pre>
        </div>
      </div>
    </div>
  </div>

  <script>
    let createdEmpId = null;

    async function testConnection() {
      const res = await fetch('test_connection.php');
      const data = await res.json();
      document.getElementById('status0').textContent = `[${res.status} ${res.statusText}]`;
      document.getElementById('status0').className = `response-status status-${res.status}`;
      document.getElementById('resp0').textContent = JSON.stringify(data, null, 2);
    }

    async function testGetAll() {
      const res = await fetch('employee_api.php');
      const data = await res.json();
      document.getElementById('status1').textContent = `[${res.status} ${res.statusText}]`;
      document.getElementById('status1').className = `response-status status-${res.status}`;
      document.getElementById('resp1').textContent = JSON.stringify(data, null, 2);
    }

    async function testGetById() {
      const res = await fetch('employee_api.php?id=1');
      const data = await res.json();
      document.getElementById('status2').textContent = `[${res.status} ${res.statusText}]`;
      document.getElementById('status2').className = `response-status status-${res.status}`;
      document.getElementById('resp2').textContent = JSON.stringify(data, null, 2);
    }

    async function testPost() {
      const payload = {
        name: "Juan Dela Cruz",
        email: "juan.dc." + Date.now() + "@example.com",
        position: "Software Developer",
        salary: 35000
      };
      const res = await fetch('employee_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      createdEmpId = data.id || null;
      if (createdEmpId) {
        document.getElementById('deleteUrl').textContent = `http://localhost/employee_management/employee_api.php?id=${createdEmpId}`;
        document.getElementById('deleteTargetPre').textContent = `id: ${createdEmpId}`;
      }
      document.getElementById('status3').textContent = `[${res.status} ${res.statusText}]`;
      document.getElementById('status3').className = `response-status status-${res.status}`;
      document.getElementById('resp3').textContent = JSON.stringify(data, null, 2);
    }

    async function testPut() {
      const payload = {
        id: 1,
        name: "Juan Dela Cruz",
        email: "juan.updated@example.com",
        position: "Senior Developer",
        salary: 45000
      };
      const res = await fetch('employee_api.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      document.getElementById('status4').textContent = `[${res.status} ${res.statusText}]`;
      document.getElementById('status4').className = `response-status status-${res.status}`;
      document.getElementById('resp4').textContent = JSON.stringify(data, null, 2);
    }

    async function testDelete() {
      const targetId = createdEmpId || 8;
      const res = await fetch(`employee_api.php?id=${targetId}`, { method: 'DELETE' });
      const data = await res.json();
      document.getElementById('status5').textContent = `[${res.status} ${res.statusText}]`;
      document.getElementById('status5').className = `response-status status-${res.status}`;
      document.getElementById('resp5').textContent = JSON.stringify(data, null, 2);
    }

    async function runAllTests() {
      await testConnection();
      await testGetAll();
      await testGetById();
      await testPost();
      await testPut();
      await testDelete();
    }

    // Auto-run on load
    window.addEventListener('DOMContentLoaded', () => {
      runAllTests();
    });
  </script>
</body>
</html>
