document.addEventListener('DOMContentLoaded', function() {
    // Dropdown profil admin
    const profileBtn = document.querySelector('.admin-profile-btn');
    const dropdown = document.querySelector('.admin-profile-dropdown');

    if (profileBtn && dropdown) {
        profileBtn.addEventListener('click', () => {
            dropdown.classList.toggle('show');
        });

        window.addEventListener('click', e => {
            if (!profileBtn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    }

    // Inisialisasi chart bisa ditambahkan di sini
});