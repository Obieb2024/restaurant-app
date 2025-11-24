<?php
function rupiah($angka){
    return 'Rp '.number_format($angka,0,',','.');
}

function is_logged_in() {
    return isset($_SESSION['user']);
}

function is_admin() {
    return is_logged_in() && $_SESSION['admin']['role'] === 'admin';
}

function is_customer() {
    return is_logged_in() && $_SESSION['user']['role'] === 'customer';
}