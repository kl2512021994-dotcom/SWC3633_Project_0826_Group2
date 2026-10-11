/* ==========================================================
   Event Ticketing System — shared script
   Loaded by every page after style.css
   ========================================================== */
const API_BASE = 'http://localhost/event-api';

/* ---------- small helpers ---------- */
const $ = id => document.getElementById(id);
const num = v => parseInt(v, 10);
const same = (a, b) => num(a) === num(b);
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const money = v => `MYR ${(parseFloat(v) || 0).toFixed(2)}`;
const fmtDateTime = d => d ? String(d).replace('T', ' ').substring(0, 16) : 'N/A';
const fmtDate = d => d ? String(d).substring(0, 10) : 'N/A';
const isPast = d => { const x = new Date(d); return !isNaN(x) && x < new Date(); };
const setText = (id, v) => { const el = $(id); if (el) el.innerText = v; };
const listFrom = data => Array.isArray(data) ? data : (data && data.data) || [];

/* ---------- API ---------- */
async function api(path, { method = 'GET', body } = {}) {
  const res = await fetch(API_BASE + path, {
    method,
    headers: body ? { 'Content-Type': 'application/json' } : undefined,
    body: body ? JSON.stringify(body) : undefined
  });
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  const text = await res.text();
  try { return text ? JSON.parse(text) : null; } catch { return text; }
}

async function fetchList(path) {
  try { return listFrom(await api(path)); }
  catch (e) { toast(`Failed to load ${path.replace('/', '')}: ${e.message}`, 'error'); return []; }
}

/* ---------- toasts ---------- */
function toast(msg, type = 'info') {
  let root = $('toast-root');
  if (!root) { root = document.createElement('div'); root.id = 'toast-root'; document.body.appendChild(root); }
  const el = document.createElement('div');
  el.className = `toast toast-${type}`;
  el.textContent = msg;
  root.appendChild(el);
  setTimeout(() => el.remove(), 4000);
}

/* ---------- auth & navigation ---------- */
function redirectByRole(role) {
  const pages = { Administrator: 'admin-dashboard.html', Organiser: 'organiser-dashboard.html', Customer: 'customer-dashboard.html' };
  if (pages[role]) window.location.href = pages[role];
}

/** Returns true when the signed-in role is allowed; otherwise redirects to login. */
function requireRole(allowed, label) {
  const role = localStorage.getItem('role');
  if (!allowed.includes(role)) {
    alert(`Access denied: ${label} login required.`);
    window.location.href = 'login.html';
    return false;
  }
  setText('lbl-name', localStorage.getItem('full_name') || label);
  return true;
}

function logout() { localStorage.clear(); window.location.href = 'login.html'; }

function switchTab(tabName, btn) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
  $(`tab-${tabName}`).classList.add('active');
  btn.classList.add('active');
}

/* ---------- shared rendering helpers ---------- */
function statusBadge(status) {
  const cls = status === 'Completed' ? 'badge-completed' : status === 'Cancelled' ? 'badge-cancelled' : 'badge-upcoming';
  return `<span class="badge ${cls}">${esc(status || 'Upcoming')}</span>`;
}

function roleBadge(role) {
  const cls = role === 'Administrator' ? 'badge-admin' : role === 'Organiser' ? 'badge-organiser' : 'badge-customer';
  return `<span class="badge ${cls}">${esc(role)}</span>`;
}

function emptyRow(cols, msg) { return `<tr><td colspan="${cols}" class="empty">${msg}</td></tr>`; }

/** Render rows into a tbody. rowFn returns an HTML string per item. */
function renderRows(tbodyId, data, cols, emptyMsg, rowFn) {
  $(tbodyId).innerHTML = data.length ? data.map(rowFn).join('') : emptyRow(cols, emptyMsg);
}

/** Stable sort that pushes items matching `isDone` to the bottom. */
function pastLast(list, isDone, tieBreak = () => 0) {
  return list.sort((a, b) => {
    const x = isDone(a), y = isDone(b);
    if (x && !y) return 1;
    if (!x && y) return -1;
    return tieBreak(a, b);
  });
}

function gcalUrl(title, desc, venue, dateStr) {
  const start = new Date(dateStr);
  if (isNaN(start)) return '#';
  const end = new Date(start.getTime() + 2 * 60 * 60 * 1000);
  const fmt = d => d.toISOString().replace(/-|:|\.\d\d\d/g, '');
  return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(title)}&dates=${fmt(start)}/${fmt(end)}&details=${encodeURIComponent(desc || '')}&location=${encodeURIComponent(venue || '')}`;
}
