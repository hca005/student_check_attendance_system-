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
  document.getElementById(id).classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
  document.body.style.overflow = '';
}
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeModal(overlay.id);
  });
});
</script>
</body>
</html>