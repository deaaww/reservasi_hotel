<?php
session_start();
require_once 'config/database.php';
require_once 'functions/function.php';

$db = new Database();
$conn = $db->connect();

$hal = isset($_GET['page']) ? sanitize_input($_GET['page']) : 'dashboard';
$page = $hal;

//statistik dashboard
if ($hal == 'dashboard') {
    try {
        $conn->exec("REFRESH MATERIALIZED VIEW mv_statistik_hotel");
        
        $stmt = $conn->query("SELECT * FROM mv_statistik_hotel");
        $stats = $stmt->fetch();
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}

include 'includes/header.php';

switch($hal) {
    case 'dashboard':
        include 'views/dashboard.php';
        break;
    case 'kamar':
        include 'views/kamar.php';
        break;
    case 'tipe_kamar':
        include 'views/tipe_kamar.php';
        break;
    case 'reservasi':
        include 'views/reservasi.php';
        break;
    case 'reservasi_form':
        include 'views/reservasi_form.php';
        break;
    case 'checkin':
        include 'views/checkin.php';
        break;
    case 'checkout':
        include 'views/checkout.php';
        break;
    case 'tamu':
        include 'views/tamu.php';
        break;
    case 'pembayaran':
        include 'views/pembayaran.php';
        break;
    case 'laporan':
        include 'views/laporan.php';
        break;
    default:
        echo "<h2>page not found</h2>";
}

include 'includes/footer.php';
?>