// Shared ICT Assist scripts
document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) {
    lucide.createIcons();
  }
});

function switchView(role) {
  window.location.href = 'switch_view.php?role=' + encodeURIComponent(role);
}
