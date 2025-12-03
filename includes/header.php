<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Reservasi Hotel - Admin Panel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-2 px-0 sidebar">
                <div class="brand-title">
                    <h4><i class="bi bi-buildings-fill me-"></i>Giggle Suites</h4>
                    <small class="text-white">Luxury Hotel Management</small>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link <?php echo $page == 'dashboard' ? 'active' : ''; ?>" href="?page=dashboard">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link <?php echo $page == 'kamar' ? 'active' : ''; ?>" href="?page=kamar">
                        <i class="bi bi-door-open"></i> Data Kamar
                    </a>
                    <a class="nav-link <?php echo $page == 'tipe_kamar' ? 'active' : ''; ?>" href="?page=tipe_kamar">
                        <i class="bi bi-grid-3x3"></i> Tipe Kamar
                    </a>
                    <a class="nav-link <?php echo $page == 'reservasi' ? 'active' : ''; ?>" href="?page=reservasi">
                        <i class="bi bi-calendar-check"></i> Reservasi
                    </a>
                    <a class="nav-link <?php echo $page == 'checkin' ? 'active' : ''; ?>" href="?page=checkin">
                        <i class="bi bi-box-arrow-in-right"></i> Check-in
                    </a>
                    <a class="nav-link <?php echo $page == 'checkout' ? 'active' : ''; ?>" href="?page=checkout">
                        <i class="bi bi-box-arrow-right"></i> Check-out
                    </a>
                    <a class="nav-link <?php echo $page == 'tamu' ? 'active' : ''; ?>" href="?page=tamu">
                        <i class="bi bi-people"></i> Data Tamu
                    </a>
                    <a class="nav-link <?php echo $page == 'pembayaran' ? 'active' : ''; ?>" href="?page=pembayaran">
                        <i class="bi bi-credit-card"></i> Pembayaran
                    </a>
                    <hr class="text-white">
                    <a class="nav-link <?php echo $page == 'laporan' ? 'active' : ''; ?>" href="?page=laporan">
                        <i class="bi bi-file-earmark-bar-graph"></i> Laporan
                    </a>
                </nav>
            </div>
            
            <!-- Main Content Area -->
            <div class="col-md-10">
                <div class="content-area">