document.addEventListener('DOMContentLoaded', function() {
  // Toggle sidebar aktif
  const sidebarLinks = document.querySelectorAll('.customer-sidebar a');
  sidebarLinks.forEach(link => {
    if (link.href === window.location.href) {
      link.classList.add('active');
    }
  });

  // Toggle password visibility (jika ada)
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', e => {
      const input = e.target.previousElementSibling || e.target.parentElement.querySelector('input');
      if (input.type === 'password') input.type = 'text';
      else input.type = 'password';
    });
  });
});