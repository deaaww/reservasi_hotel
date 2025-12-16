<h1 class="page-title">Dashboard</h1>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-door-open"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1">Total Kamar</h6>
                    <h3 class="mb-0"><?php echo isset($stats['total_kamar']) ? $stats['total_kamar'] : 0; ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1">Kamar Tersedia</h6>
                    <h3 class="mb-0"><?php echo isset($stats['kamar_tersedia']) ? $stats['kamar_tersedia'] : 0; ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                    <i class="bi bi-bookmark-fill"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1">Kamar Terisi</h6>
                    <h3 class="mb-0"><?php echo isset($stats['kamar_terisi']) ? $stats['kamar_terisi'] : 0; ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1">Total Tamu</h6>
                    <h3 class="mb-0"><?php echo isset($stats['total_tamu']) ? $stats['total_tamu'] : 0; ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted mb-3">Reservasi Pending</h6>
                <h2 class="mb-0 text-warning"><?php echo isset($stats['reservasi_pending']) ? $stats['reservasi_pending'] : 0; ?></h2>
                <small class="text-muted">Menunggu konfirmasi</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted mb-3">Reservasi Confirmed</h6>
                <h2 class="mb-0 text-info"><?php echo isset($stats['reservasi_confirmed']) ? $stats['reservasi_confirmed'] : 0; ?></h2>
                <small class="text-muted">Sudah dikonfirmasi</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted mb-3">Tamu Check-in</h6>
                <h2 class="mb-0 text-primary"><?php echo isset($stats['tamu_checkin']) ? $stats['tamu_checkin'] : 0; ?></h2>
                <small class="text-muted">Sedang menginap</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-12">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted mb-3">
                    <i class="bi bi-cash-stack me-2"></i>Pendapatan Bulan Ini
                </h6>
                <h2 class="mb-0 text-success">
                    <?php echo format_rupiah(isset($stats['pendapatan_bulan_ini']) ? $stats['pendapatan_bulan_ini'] : 0); ?>
                </h2>
                <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>
                    Update terakhir: <?php echo date('d M Y H:i', isset($stats['last_refresh']) ? strtotime($stats['last_refresh']) : time()); ?>
                </small>
            </div>
        </div>
    </div>
</div>

<?php

$reservasi_terbaru = [];
$error = null;

//reservasi terbaru
try {
    if (isset($conn)) {
        $stmt = $conn->query("
            SELECT * FROM v_reservasi_detail 
            ORDER BY tgl_checkin DESC 
            LIMIT 5
        ");
        $reservasi_terbaru = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $error = $e->getMessage();
    error_log("Database error: " . $e->getMessage());
}
?>

<div class="card table-card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Reservasi Terbaru</h5>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Terjadi kesalahan saat mengambil data: <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif (empty($reservasi_terbaru)): ?>
            <div class="text-center text-muted py-4">
                <i class="bi bi-calendar-x fs-1"></i>
                <p class="mt-2">Tidak ada data reservasi</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tamu</th>
                            <th>Kamar</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservasi_terbaru as $reserv): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($reserv['id_reservasi'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($reserv['nama_tamu'] ?? 'N/A'); ?></td>
                            <td>
                                <?php echo htmlspecialchars($reserv['nomor_kamar'] ?? 'N/A'); ?> - 
                                <?php echo htmlspecialchars($reserv['nama_tipe'] ?? 'N/A'); ?>
                            </td>
                            <td><?php echo isset($reserv['tgl_checkin']) ? date('d/m/Y', strtotime($reserv['tgl_checkin'])) : 'N/A'; ?></td>
                            <td><?php echo isset($reserv['tgl_checkout']) ? date('d/m/Y', strtotime($reserv['tgl_checkout'])) : 'N/A'; ?></td>
                            <td><?php echo isset($reserv['total_harga']) ? format_rupiah($reserv['total_harga']) : 'N/A'; ?></td>
                            <td><?php echo isset($reserv['status_reservasi']) ? status_badge($reserv['status_reservasi']) : 'N/A'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>