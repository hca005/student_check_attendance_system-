</div><!-- /.page-body -->
</div><!-- /.main-content -->
</div><!-- /.app-shell -->

<!-- Toast container -->
<div id="toast-container"></div>

<script>
// ── Sidebar mobile ──────────────────────────────
function openSidebar() {
  document.getElementById('sidebar').classList.add('open');
  document.getElementById('sidebarOverlay').classList.add('show');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('show');
}

// ── Toast helper ────────────────────────────────
function showToast(message, type = 'info') {
  const icons = {
    success: '✓', danger: '✕', info: 'ℹ'
  };
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `<span>${icons[type]||'ℹ'}</span> ${message}`;
  document.getElementById('toast-container').appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
}

// ── Show toast from URL param ───────────────────
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('success')) showToast(decodeURIComponent(urlParams.get('success')), 'success');
if (urlParams.get('error'))   showToast(decodeURIComponent(urlParams.get('error')),   'danger');
</script>
</body>
</html>