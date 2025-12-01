<?php
session_start();
require_once 'config/database.php';
require_once 'functions/helpers.php';

$db = new Database();
$conn = $db->connect();

// Get page parameter
$page = isset($_GET['page']) ? sanitize_input($_GET['page']) : 'dashboard';

// Get statistics for dashboard
if ($page == 'dashboard') {
    try {
        // Refresh materialized view
        $conn->exec("REFRESH MATERIALIZED VIEW mv_statistik_hotel");
        
        $stmt = $conn->query("SELECT * FROM mv_statistik_hotel");
        $stats = $stmt->fetch();
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}

// Include header
include 'includes/header.php';

// Route to different pages
switch($page) {
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
        echo "<h2>Page not found</h2>";
}

// Include footer
include 'includes/footer.php';
?>