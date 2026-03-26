// API Tester — Main JS

const App = {
  currentPage: 'tester',
  historyFilters: { method: 'ALL', status: '' },
  headers: [],

  init() {
    this.bindNav();
    this.bindSendRequest();
    this.bindHeaderEditor();
    this.bindMethodColor();
    this.bindTabSwitcher();
    this.loadCollections();
    this.loadHistory();
    this.loadStats();
  },

  // ─── NAVIGATION ───
  bindNav() {
    document.querySelectorAll('.nav-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const page = btn.dataset.page;
        this.showPage(page);
      });
    });
  },

  showPage(page) {
    this.currentPage = page;
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('page-' + page)?.classList.add('active');
    document.querySelector(`.nav-btn[data-page="${page}"]`)?.classList.add('active');
    if (page === 'history') this.loadHistory();
    if (page === 'tester') this.loadCollections();
  },

  // ─── METHOD COLOR ───
  bindMethodColor() {
    const sel = document.getElementById('method-select');
    const colors = { GET:'#34d399', POST:'#60a5fa', PUT:'#fbbf24', PATCH:'#a78bfa', DELETE:'#f87171', HEAD:'#22d3ee' };
    const update = () => { sel.style.color = colors[sel.value] || '#e8eaf6'; };
    sel.addEventListener('change', update);
    update();
  },

  // ─── TABS ───
  bindTabSwitcher() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const group = btn.closest('.tabs').dataset.group;
        const target = btn.dataset.tab;
        btn.closest('.tabs').querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll(`.tab-content[data-group="${group}"]`).forEach(c => c.classList.remove('active'));
        document.querySelector(`.tab-content[data-group="${group}"][data-tab="${target}"]`)?.classList.add('active');
      });
    });
  },

  // ─── HEADER EDITOR ───
  bindHeaderEditor() {
    document.getElementById('add-header-btn').addEventListener('click', () => this.addHeaderRow());
    this.addHeaderRow('Content-Type', 'application/json');
  },

  addHeaderRow(key = '', value = '') {
    const container = document.getElementById('headers-container');
    const row = document.createElement('div');
    row.className = 'header-row';
    row.innerHTML = `
      <input class="header-input" type="text" placeholder="Header name" value="${this.escHtml(key)}">
      <input class="header-input" type="text" placeholder="Value" value="${this.escHtml(value)}">
      <button class="remove-header" title="Remove">×</button>
    `;
    row.querySelector('.remove-header').addEventListener('click', () => row.remove());
    container.appendChild(row);
  },

  getHeaders() {
    const headers = {};
    document.querySelectorAll('#headers-container .header-row').forEach(row => {
      const inputs = row.querySelectorAll('.header-input');
      const key = inputs[0].value.trim();
      const val = inputs[1].value.trim();
      if (key) headers[key] = val;
    });
    return headers;
  },

  // ─── SEND REQUEST ───
  bindSendRequest() {
    document.getElementById('send-btn').addEventListener('click', () => this.sendRequest());
    document.getElementById('url-input').addEventListener('keydown', e => {
      if (e.key === 'Enter') this.sendRequest();
    });
  },

  async sendRequest() {
    const method = document.getElementById('method-select').value;
    const url    = document.getElementById('url-input').value.trim();
    const body   = document.getElementById('body-textarea').value.trim();
    const headers = this.getHeaders();

    if (!url) { this.toast('Please enter a URL', 'error'); return; }

    const btn = document.getElementById('send-btn');
    btn.classList.add('loading');
    btn.innerHTML = '<div class="spinner"></div> Sending...';

    this.showResponseLoading();

    const formData = new FormData();
    formData.append('action', 'send_request');
    formData.append('method', method);
    formData.append('url', url);
    formData.append('headers', JSON.stringify(headers));
    formData.append('body', body);

    try {
      const res  = await fetch('api.php', { method: 'POST', body: formData });
      const data = await res.json();
      this.showResponse(data);
      this.loadStats();
    } catch (err) {
      this.showResponseError(err.message);
    }

    btn.classList.remove('loading');
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M2 12L22 2L16 22L11 13Z"/></svg> Send';
  },

  showResponseLoading() {
    document.getElementById('response-empty').style.display = 'none';
    document.getElementById('response-result').style.display = 'none';
    document.getElementById('response-loading').style.display = 'flex';
  },

  showResponse(data) {
    document.getElementById('response-loading').style.display = 'none';
    document.getElementById('response-empty').style.display = 'none';
    const result = document.getElementById('response-result');
    result.style.display = 'flex';

    if (!data.success && data.error) {
      result.querySelector('.response-meta').innerHTML = `<span class="status-badge status-0">Error</span><span class="meta-chip">${this.escHtml(data.error)}</span>`;
      result.querySelector('.response-body').innerHTML = this.escHtml(data.error);
      return;
    }

    const status = data.status || 0;
    const cls    = status >= 500 ? 'status-5xx' : status >= 400 ? 'status-4xx' : status >= 300 ? 'status-3xx' : status >= 200 ? 'status-2xx' : 'status-0';
    const dotCls = status >= 400 ? 'dot-red' : status >= 300 ? 'dot-yellow' : 'dot-green';
    const statusText = this.httpStatusText(status);

    result.querySelector('.response-meta').innerHTML = `
      <span class="status-badge ${cls}">${status} ${statusText}</span>
      <span class="meta-chip"><span class="dot ${dotCls}"></span>${data.time_ms}ms</span>
      ${data.content_type ? `<span class="meta-chip">${this.escHtml(data.content_type.split(';')[0])}</span>` : ''}
      ${data.body ? `<span class="meta-chip">${this.formatBytes(data.body.length)}</span>` : ''}
    `;

    const bodyEl = result.querySelector('.response-body');
    const formatted = data.body_formatted || data.body || '';
    bodyEl.innerHTML = data.is_json ? this.syntaxHighlight(formatted) : this.escHtml(formatted);

    // Response headers tab
    const rhEl = result.querySelector('.response-headers-body');
    if (rhEl && data.response_headers) {
      rhEl.innerHTML = this.escHtml(JSON.stringify(data.response_headers, null, 2));
    }
  },

  showResponseError(msg) {
    document.getElementById('response-loading').style.display = 'none';
    document.getElementById('response-empty').style.display = 'none';
    const result = document.getElementById('response-result');
    result.style.display = 'flex';
    result.querySelector('.response-meta').innerHTML = `<span class="status-badge status-0">Error</span>`;
    result.querySelector('.response-body').textContent = msg;
  },

  // ─── COLLECTIONS ───
  async loadCollections() {
    try {
      const res  = await fetch('api.php?action=get_collections');
      const data = await res.json();
      this.renderCollections(data);
    } catch (_) {}
  },

  renderCollections(collections) {
    const list = document.getElementById('collections-list');
    if (!collections.length) {
      list.innerHTML = `<div class="empty-state" style="padding:30px 12px"><div class="icon">📁</div><p>No saved requests</p><small>Save a request to reuse it</small></div>`;
      return;
    }
    list.innerHTML = collections.map(c => `
      <div class="collection-item" data-id="${c.id}">
        <span class="method-pill pill-${c.method.toLowerCase()}">${c.method}</span>
        <div class="col-info">
          <div class="col-name">${this.escHtml(c.name)}</div>
          <div class="col-url">${this.escHtml(c.url)}</div>
        </div>
        <button class="del-btn" data-id="${c.id}" title="Delete">×</button>
      </div>
    `).join('');

    list.querySelectorAll('.collection-item').forEach(item => {
      item.addEventListener('click', (e) => {
        if (e.target.classList.contains('del-btn')) return;
        this.loadCollectionIntoEditor(item.dataset.id);
      });
    });

    list.querySelectorAll('.del-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        this.deleteCollection(btn.dataset.id);
      });
    });
  },

  async loadCollectionIntoEditor(id) {
    try {
      const res  = await fetch(`api.php?action=get_collections`);
      const all  = await res.json();
      const col  = all.find(c => c.id == id);
      if (!col) return;

      document.getElementById('url-input').value = col.url;
      document.getElementById('method-select').value = col.method;
      document.getElementById('method-select').dispatchEvent(new Event('change'));
      document.getElementById('body-textarea').value = col.request_body || '';

      // Load headers
      const container = document.getElementById('headers-container');
      container.innerHTML = '';
      try {
        const h = JSON.parse(col.request_headers || '{}');
        Object.entries(h).forEach(([k, v]) => this.addHeaderRow(k, v));
      } catch (_) {}

      this.showPage('tester');
      this.toast('Request loaded', 'success');
    } catch (_) {}
  },

  async deleteCollection(id) {
    const fd = new FormData();
    fd.append('action', 'delete_collection');
    fd.append('id', id);
    try {
      await fetch('api.php', { method: 'POST', body: fd });
      this.loadCollections();
      this.toast('Collection deleted', 'success');
    } catch (_) {}
  },

  openSaveModal() {
    const url    = document.getElementById('url-input').value.trim();
    const method = document.getElementById('method-select').value;
    document.getElementById('modal-method').textContent = method;
    document.getElementById('modal-url').textContent    = url || 'No URL set';
    document.getElementById('save-modal').classList.add('show');
    document.getElementById('collection-name').focus();
  },

  closeSaveModal() {
    document.getElementById('save-modal').classList.remove('show');
  },

  async saveCollection() {
    const name   = document.getElementById('collection-name').value.trim();
    const desc   = document.getElementById('collection-desc').value.trim();
    const method = document.getElementById('method-select').value;
    const url    = document.getElementById('url-input').value.trim();
    const body   = document.getElementById('body-textarea').value;
    const headers = JSON.stringify(this.getHeaders());

    if (!name) { this.toast('Please enter a name', 'error'); return; }
    if (!url)  { this.toast('Please enter a URL', 'error');  return; }

    const fd = new FormData();
    fd.append('action', 'save_collection');
    fd.append('name', name);
    fd.append('description', desc);
    fd.append('method', method);
    fd.append('url', url);
    fd.append('headers', headers);
    fd.append('body', body);

    try {
      const res  = await fetch('api.php', { method: 'POST', body: fd });
      const data = await res.json();
      if (data.success) {
        this.closeSaveModal();
        this.loadCollections();
        this.toast('Saved to collections!', 'success');
        document.getElementById('collection-name').value = '';
        document.getElementById('collection-desc').value = '';
      }
    } catch (_) {}
  },

  // ─── HISTORY ───
  async loadHistory() {
    const method = this.historyFilters.method !== 'ALL' ? this.historyFilters.method : '';
    const status = this.historyFilters.status;
    const url    = `api.php?action=get_history&method=${method}&status=${status}`;
    try {
      const res  = await fetch(url);
      const data = await res.json();
      this.renderHistory(data);
    } catch (_) {}
  },

  renderHistory(rows) {
    const tbody = document.getElementById('history-tbody');
    if (!rows.length) {
      tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state"><div class="icon">📋</div><p>No requests yet</p><small>Send a request to see it here</small></div></td></tr>`;
      return;
    }

    tbody.innerHTML = rows.map(r => {
      const status = r.response_status;
      const cls    = status >= 500 ? 'status-5xx' : status >= 400 ? 'status-4xx' : status >= 300 ? 'status-3xx' : status >= 200 ? 'status-2xx' : 'status-0';
      const date   = new Date(r.created_at).toLocaleString();
      return `
        <tr onclick="App.showLogDetail(${r.id})">
          <td><span class="method-pill pill-${r.method.toLowerCase()}">${r.method}</span></td>
          <td class="url-cell" title="${this.escHtml(r.url)}">${this.escHtml(r.url)}</td>
          <td><span class="status-badge ${cls}">${status}</span></td>
          <td class="time-cell">${r.response_time_ms}ms</td>
          <td class="time-cell">${date}</td>
        </tr>
      `;
    }).join('');
  },

  setHistoryFilter(type, value) {
    this.historyFilters[type] = value;
    // Update active filter buttons
    document.querySelectorAll(`.filter-btn[data-filter-type="${type}"]`).forEach(btn => {
      btn.classList.toggle('active', btn.dataset.filterValue === value);
    });
    this.loadHistory();
  },

  async clearHistory() {
    if (!confirm('Clear all request history?')) return;
    const fd = new FormData();
    fd.append('action', 'clear_history');
    await fetch('api.php', { method: 'POST', body: fd });
    this.loadHistory();
    this.loadStats();
    this.toast('History cleared', 'success');
  },

  exportHistory() {
    window.open('api.php?action=export_history', '_blank');
  },

  // ─── LOG DETAIL DRAWER ───
  async showLogDetail(id) {
    try {
      const res  = await fetch(`api.php?action=get_log_detail&id=${id}`);
      const data = await res.json();
      if (!data) return;

      const drawer = document.getElementById('detail-drawer');
      document.getElementById('drawer-method').textContent = data.method;
      document.getElementById('drawer-method').className   = `method-pill pill-${data.method.toLowerCase()}`;
      document.getElementById('drawer-url').textContent    = data.url;
      document.getElementById('drawer-status').textContent = data.response_status;
      document.getElementById('drawer-time').textContent   = data.response_time_ms + 'ms';
      document.getElementById('drawer-date').textContent   = new Date(data.created_at).toLocaleString();

      const reqHeaders = JSON.parse(data.request_headers || '{}');
      document.getElementById('drawer-req-headers').textContent = JSON.stringify(reqHeaders, null, 2);
      document.getElementById('drawer-req-body').textContent = data.request_body || '(empty)';

      const resHeaders = JSON.parse(data.response_headers || '{}');
      document.getElementById('drawer-res-headers').textContent = JSON.stringify(resHeaders, null, 2);

      const resBody = document.getElementById('drawer-res-body');
      resBody.innerHTML = data.is_json ? this.syntaxHighlight(data.body_formatted) : this.escHtml(data.response_body || '');

      drawer.classList.add('show');
    } catch (_) {}
  },

  closeDrawer() {
    document.getElementById('detail-drawer').classList.remove('show');
  },

  // ─── STATS ───
  async loadStats() {
    try {
      const res  = await fetch('api.php?action=get_history&limit=1000');
      const data = await res.json();

      const total   = data.length;
      const success = data.filter(r => r.response_status >= 200 && r.response_status < 300).length;
      const avgTime = total ? Math.round(data.reduce((a, r) => a + r.response_time_ms, 0) / total) : 0;
      const errors  = data.filter(r => r.response_status >= 400).length;

      document.getElementById('stat-total').textContent   = total;
      document.getElementById('stat-success').textContent = success;
      document.getElementById('stat-avg').textContent     = avgTime + 'ms';
      document.getElementById('stat-errors').textContent  = errors;
    } catch (_) {}
  },

  // ─── COPY RESPONSE ───
  copyResponse() {
    const body = document.getElementById('response-body').innerText;
    navigator.clipboard.writeText(body).then(() => this.toast('Copied!', 'success'));
  },

  // ─── JSON SYNTAX HIGHLIGHT ───
  syntaxHighlight(json) {
    const escaped = this.escHtml(json);
    return escaped.replace(
      /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g,
      match => {
        let cls = 'json-number';
        if (/^"/.test(match)) {
          cls = /:$/.test(match) ? 'json-key' : 'json-string';
        } else if (/true|false/.test(match)) {
          cls = 'json-boolean';
        } else if (/null/.test(match)) {
          cls = 'json-null';
        }
        return `<span class="${cls}">${match}</span>`;
      }
    );
  },

  // ─── TOAST ───
  toast(msg, type = 'success') {
    const container = document.getElementById('toast-container');
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `${type === 'success' ? '✓' : '✕'} ${msg}`;
    container.appendChild(t);
    setTimeout(() => t.remove(), 3000);
  },

  // ─── HELPERS ───
  escHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  },

  formatBytes(b) {
    if (b < 1024) return b + ' B';
    if (b < 1024 * 1024) return (b / 1024).toFixed(1) + ' KB';
    return (b / 1024 / 1024).toFixed(1) + ' MB';
  },

  httpStatusText(code) {
    const map = {
      200:'OK', 201:'Created', 204:'No Content', 301:'Moved', 302:'Found',
      400:'Bad Request', 401:'Unauthorized', 403:'Forbidden', 404:'Not Found',
      405:'Method Not Allowed', 422:'Unprocessable Entity', 429:'Too Many Requests',
      500:'Server Error', 502:'Bad Gateway', 503:'Unavailable', 504:'Gateway Timeout'
    };
    return map[code] || '';
  }
};

document.addEventListener('DOMContentLoaded', () => App.init());
