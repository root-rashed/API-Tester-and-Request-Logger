<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Tester — Request Logger</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ─── TOPBAR ─── -->
<header class="topbar">
  <a href="#" class="logo">
    <div class="logo-icon">⚡</div>
    API<span>Tester</span>
  </a>

  <nav class="topbar-nav">
    <button class="nav-btn active" data-page="tester">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2L15 22L11 13L2 9Z"/></svg>
      Tester
    </button>
    <button class="nav-btn" data-page="history">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      History
    </button>
  </nav>

  <div class="topbar-right">
    <span class="badge">v1.0</span>
    <span class="badge" style="color: var(--green); border-color: rgba(52,211,153,0.3);">● Live</span>
  </div>
</header>

<!-- ─── MAIN LAYOUT ─── -->
<div class="layout">

  <!-- ─── SIDEBAR ─── -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <span class="sidebar-title">Collections</span>
      <button class="btn-icon" onclick="App.openSaveModal()" title="Save current request">+</button>
    </div>
    <div class="sidebar-body" id="collections-list">
      <div class="empty-state" style="padding:30px 12px">
        <div class="icon">📁</div>
        <p>No saved requests</p>
        <small>Save a request to reuse it</small>
      </div>
    </div>
  </aside>

  <!-- ─── CONTENT ─── -->
  <main class="main">

    <!-- ══ TESTER PAGE ══ -->
    <div class="page active" id="page-tester">

      <!-- Request Panel -->
      <div class="request-panel">
        <div class="panel-label">Request</div>

        <!-- URL Bar -->
        <div class="url-bar">
          <select id="method-select" class="method-select">
            <option value="GET">GET</option>
            <option value="POST">POST</option>
            <option value="PUT">PUT</option>
            <option value="PATCH">PATCH</option>
            <option value="DELETE">DELETE</option>
            <option value="HEAD">HEAD</option>
          </select>
          <input id="url-input" class="url-input" type="text" placeholder="https://api.example.com/endpoint" autocomplete="off" spellcheck="false">
          <button id="send-btn" class="send-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M2 12L22 2L16 22L11 13Z"/></svg>
            Send
          </button>
        </div>

        <!-- Request Tabs -->
        <div class="tabs" data-group="request">
          <button class="tab-btn active" data-tab="headers">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            Headers
          </button>
          <button class="tab-btn" data-tab="body">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
            Body
          </button>
          <button class="tab-btn" data-tab="auth">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Auth
          </button>
        </div>

        <!-- Headers Tab -->
        <div class="tab-content active" data-group="request" data-tab="headers">
          <div class="headers-editor" id="headers-container"></div>
          <button class="add-header-btn" id="add-header-btn">+ Add Header</button>
        </div>

        <!-- Body Tab -->
        <div class="tab-content" data-group="request" data-tab="body">
          <textarea id="body-textarea" class="body-textarea" placeholder='{"key": "value"}'></textarea>
        </div>

        <!-- Auth Tab -->
        <div class="tab-content" data-group="request" data-tab="auth">
          <div style="margin-bottom:10px">
            <label class="panel-label" style="display:block;margin-bottom:6px">Bearer Token</label>
            <div style="display:flex;gap:8px">
              <input id="auth-token" class="url-input" type="text" placeholder="Enter your bearer token..." style="height:40px">
              <button class="btn btn-ghost" onclick="App.applyBearerToken()" style="height:40px;white-space:nowrap">Apply</button>
            </div>
          </div>
        </div>

        <!-- Save to collection -->
        <div style="margin-top:10px;display:flex;justify-content:flex-end">
          <button class="btn btn-ghost" onclick="App.openSaveModal()" style="font-size:11px">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save to collections
          </button>
        </div>
      </div>

      <!-- Response Panel -->
      <div class="response-panel">
        <div class="panel-label">Response</div>

        <!-- Empty state -->
        <div id="response-empty" class="response-empty">
          <div class="icon">🚀</div>
          <p>No response yet</p>
          <small>Enter a URL and click Send</small>
        </div>

        <!-- Loading state -->
        <div id="response-loading" style="display:none;flex:1;align-items:center;justify-content:center;gap:12px;color:var(--text2)">
          <div class="spinner" style="border-color:rgba(167,139,250,0.3);border-top-color:var(--accent2)"></div>
          <span style="font-size:13px;font-weight:600">Sending request...</span>
        </div>

        <!-- Result state -->
        <div id="response-result" style="display:none;flex-direction:column;flex:1;gap:12px">
          <div class="response-meta"></div>

          <!-- Response Tabs -->
          <div class="tabs" data-group="response">
            <button class="tab-btn active" data-tab="body">Body</button>
            <button class="tab-btn" data-tab="res-headers">Headers</button>
          </div>

          <div class="tab-content active" data-group="response" data-tab="body">
            <div class="response-body-wrap">
              <button class="copy-btn" onclick="App.copyResponse()">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                Copy
              </button>
              <pre id="response-body" class="response-body"></pre>
            </div>
          </div>

          <div class="tab-content" data-group="response" data-tab="res-headers">
            <div class="response-body-wrap">
              <pre class="response-body response-headers-body"></pre>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ HISTORY PAGE ══ -->
    <div class="page" id="page-history">
      <!-- Stats Bar -->
      <div class="stats-bar">
        <div class="stat-card">
          <div class="stat-label">Total Requests</div>
          <div class="stat-value" id="stat-total">—</div>
          <div class="stat-sub">all time</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Successful</div>
          <div class="stat-value" id="stat-success" style="color:var(--green)">—</div>
          <div class="stat-sub">2xx responses</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Avg Response</div>
          <div class="stat-value" id="stat-avg" style="color:var(--accent2)">—</div>
          <div class="stat-sub">milliseconds</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Errors</div>
          <div class="stat-value" id="stat-errors" style="color:var(--red)">—</div>
          <div class="stat-sub">4xx + 5xx</div>
        </div>
      </div>

      <!-- History Content -->
      <div class="history-page">
        <div class="page-header">
          <div>
            <div class="page-title">Request History</div>
            <div class="page-subtitle">Click any row to inspect full details</div>
          </div>
          <div class="btn-group">
            <button class="btn btn-ghost" onclick="App.exportHistory()">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Export CSV
            </button>
            <button class="btn btn-danger" onclick="App.clearHistory()">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
              Clear All
            </button>
          </div>
        </div>

        <!-- Filters -->
        <div class="filters">
          <span style="font-size:11px;font-weight:700;color:var(--text2);align-self:center;letter-spacing:0.5px">METHOD:</span>
          <?php foreach(['ALL','GET','POST','PUT','PATCH','DELETE','HEAD'] as $m): ?>
          <button class="filter-btn <?= $m==='ALL'?'active':'' ?>" data-filter-type="method" data-filter-value="<?= $m ?>" onclick="App.setHistoryFilter('method', '<?= $m ?>')"><?= $m ?></button>
          <?php endforeach; ?>

          <span style="font-size:11px;font-weight:700;color:var(--text2);align-self:center;letter-spacing:0.5px;margin-left:8px">STATUS:</span>
          <?php foreach([['','All'],['2xx','2xx'],['4xx','4xx'],['5xx','5xx']] as [$v,$l]): ?>
          <button class="filter-btn <?= $v===''?'active':'' ?>" data-filter-type="status" data-filter-value="<?= $v ?>" onclick="App.setHistoryFilter('status', '<?= $v ?>')"><?= $l ?></button>
          <?php endforeach; ?>
        </div>

        <!-- Table -->
        <div class="history-table-wrap">
          <table class="history-table">
            <thead>
              <tr>
                <th>Method</th>
                <th>URL</th>
                <th>Status</th>
                <th>Time</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody id="history-tbody">
              <tr><td colspan="5"><div class="empty-state"><div class="icon">📋</div><p>No requests yet</p></div></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

<!-- ─── LOG DETAIL DRAWER ─── -->
<div class="detail-drawer" id="detail-drawer">
  <div class="drawer-header">
    <div style="display:flex;align-items:center;gap:10px">
      <span id="drawer-method" class="method-pill"></span>
      <span class="drawer-title" id="drawer-url" style="font-size:12px;font-family:'JetBrains Mono',monospace;color:var(--text1);max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"></span>
    </div>
    <button class="btn-icon" onclick="App.closeDrawer()">×</button>
  </div>
  <div class="drawer-body">
    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap">
      <span class="meta-chip">Status: <strong id="drawer-status"></strong></span>
      <span class="meta-chip">Time: <strong id="drawer-time"></strong></span>
      <span class="meta-chip" id="drawer-date" style="font-size:11px;color:var(--text2)"></span>
    </div>

    <div class="detail-section">
      <div class="detail-section-title">Request Headers</div>
      <pre class="detail-code" id="drawer-req-headers"></pre>
    </div>

    <div class="detail-section">
      <div class="detail-section-title">Request Body</div>
      <pre class="detail-code" id="drawer-req-body"></pre>
    </div>

    <div class="detail-section">
      <div class="detail-section-title">Response Headers</div>
      <pre class="detail-code" id="drawer-res-headers"></pre>
    </div>

    <div class="detail-section">
      <div class="detail-section-title">Response Body</div>
      <pre class="detail-code" id="drawer-res-body"></pre>
    </div>
  </div>
</div>

<!-- ─── SAVE COLLECTION MODAL ─── -->
<div class="modal-overlay" id="save-modal">
  <div class="modal">
    <div class="modal-title">Save to Collections</div>

    <div style="background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:10px 14px;margin-bottom:16px;font-size:12px;font-family:'JetBrains Mono',monospace;">
      <span id="modal-method" class="method-pill" style="margin-right:8px"></span>
      <span id="modal-url" style="color:var(--text1)"></span>
    </div>

    <div class="form-group">
      <label class="form-label">Name *</label>
      <input id="collection-name" class="form-input" type="text" placeholder="e.g. Get Users">
    </div>
    <div class="form-group">
      <label class="form-label">Description</label>
      <input id="collection-desc" class="form-input" type="text" placeholder="Optional description">
    </div>

    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="App.closeSaveModal()">Cancel</button>
      <button class="btn btn-primary" onclick="App.saveCollection()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save Request
      </button>
    </div>
  </div>
</div>

<!-- ─── TOAST CONTAINER ─── -->
<div class="toast-container" id="toast-container"></div>

<script src="assets/js/app.js"></script>
<script>
  // Apply bearer token helper
  App.applyBearerToken = function() {
    const token = document.getElementById('auth-token').value.trim();
    if (!token) return;
    // Find or create Authorization header row
    const rows = document.querySelectorAll('#headers-container .header-row');
    for (const row of rows) {
      const key = row.querySelectorAll('.header-input')[0];
      if (key.value.toLowerCase() === 'authorization') {
        row.querySelectorAll('.header-input')[1].value = 'Bearer ' + token;
        App.toast('Bearer token applied', 'success');
        return;
      }
    }
    App.addHeaderRow('Authorization', 'Bearer ' + token);
    App.toast('Bearer token added to headers', 'success');
  };
</script>
</body>
</html>
