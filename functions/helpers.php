<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

function format_tanggal($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $pecah = explode('-', $tanggal);
    return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0];
}

function get_status_badge($status) {
    $badges = [
        'tersedia' => 'success',
        'terisi' => 'danger',
        'maintenance' => 'warning',
        'pending' => 'warning',
        'confirmed' => 'info',
        'check-in' => 'primary',
        'selesai' => 'secondary',
        'cancelled' => 'dark',
        'lunas' => 'success',
        'belum lunas' => 'danger'
    ];
    
    $badge_class = isset($badges[$status]) ? $badges[$status] : 'secondary';
    return "<span class='badge bg-{$badge_class}'>{$status}</span>";
}
?>