<?php // views/layouts/footer.php ?>
</div><!-- /page-content -->
</main><!-- /main-content -->
</div><!-- /app-wrapper -->

<script>
// Global action menu toggle
document.querySelectorAll('.action-menu-btn').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    const menu = btn.closest('.action-menu');
    document.querySelectorAll('.action-menu.open').forEach(m => { if (m !== menu) m.classList.remove('open'); });
    menu.classList.toggle('open');
  });
});
document.addEventListener('click', () => {
  document.querySelectorAll('.action-menu.open').forEach(m => m.classList.remove('open'));
});

// Modal helpers
function openModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('open');
  document.body.style.overflow = '';
}
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeModal(overlay.id);
  });
});
document.querySelectorAll('.btn-close').forEach(btn => {
  btn.addEventListener('click', (e) => {
    const alert = btn.closest('.alert');
    if (alert) {
      alert.remove();
      return;
    }

    const modal = btn.closest('.modal-overlay');
    if (modal) {
      closeModal(modal.id);
    }
  });
});
</script>
</body>
</html>