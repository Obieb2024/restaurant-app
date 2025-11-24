document.querySelectorAll('.toggle-password').forEach(btn => {
  btn.addEventListener('click', e => {
    const input = e.target.parentElement.querySelector('input');
    if (input.type === "password") input.type = "text";
    else input.type = "password";
  });
});

const roleRadios = document.querySelectorAll('input[name=role]');
const roleLabel = document.querySelector('.role-label');
roleRadios.forEach(radio => {
  radio.addEventListener('change', () => {
    roleLabel.textContent = radio.value.charAt(0).toUpperCase() + radio.value.slice(1);
    // update button text sesuai role terpilih
const roleRadios = document.querySelectorAll('input[name="role"]');
const btnRegister = document.querySelector('.btn-register');

roleRadios.forEach(radio => {
  radio.addEventListener('change', () => {
    btnRegister.textContent = `Daftar sebagai ${radio.value.charAt(0).toUpperCase() + radio.value.slice(1)}`;
  });
});
  });
});