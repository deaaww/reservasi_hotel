<?php
$message = '';
$message_type = '';

$confirm_reservasi = [];
$tamu_checkin = [];
$error = null;

//checkin dgn store procedure
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkin'])) {
    try {
        $conn->beginTransaction();
        
        $id_reservasi = sanitize_input($_POST['id_reservasi']);
        
        $stmt = $conn->prepare("CALL proses_checkin(?)");
        $stmt->execute([$id_reservasi]);
        
        $conn->commit();
        
        $message = "Check-in berhasil! Tamu sudah dapat masuk ke kamar.";
        $message_type = "success";
        
    } catch (Exception $e) {
        $conn->rollBack();
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

//confirm reservasi
try {
    $stmt = $conn->query("
        SELECT * FROM v_reservasi_detail 
        WHERE status_reservasi = 'confirmed' 
        AND tgl_checkin <= CURRENT_DATE
        ORDER BY tgl_checkin
    ");
    $confirm_reservasi = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error mengambil data reservasi: " . $e->getMessage();
    error_log($error);
}

//tamu checkin
try {
    $stmt = $conn->query("
        SELECT * FROM v_reservasi_detail 
        WHERE status_reservasi = 'check-in' 
        ORDER BY tgl_checkin DESC
        LIMIT 10
    ");
    $tamu_checkin = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error mengambil data tamu: " . $e->getMessage();
    error_log($error);
}
?>

<h1 class="page-title">Check-in Tamu</h1>

<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
    <i class="bi bi-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card table-card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-box-arrow-in-right me-2"></i>Daftar Reservasi Siap Check-in</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($confirm_reservasi)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID Reservasi</th>
                        <th>Tamu</th>
                        <th>Kontak</th>
                        <th>Kamar</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Lama Menginap</th>
                        <th>Total Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($confirm_reservasi as $reserv): ?>
                    <tr>
                        <td><strong>#<?php echo htmlspecialchars($reserv['id_reservasi'] ?? 'N/A'); ?></strong></td>
                        <td><?php echo htmlspecialchars($reserv['nama_tamu'] ?? 'N/A'); ?></td>
                        <td>
                            <?php if (!empty($reserv['no_telp'])): ?>
                                <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($reserv['no_telp']); ?><br>
                            <?php endif; ?>
                            <?php if (!empty($reserv['email'])): ?>
                                <small class="text-muted">
                                    <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($reserv['email']); ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($reserv['nomor_kamar'] ?? 'N/A'); ?></strong><br>
                            <small><?php echo htmlspecialchars($reserv['nama_tipe'] ?? 'N/A'); ?></small>
                        </td>
                        <td><?php echo isset($reserv['tgl_checkin']) ? format_tanggal($reserv['tgl_checkin']) : 'N/A'; ?></td>
                        <td><?php echo isset($reserv['tgl_checkout']) ? format_tanggal($reserv['tgl_checkout']) : 'N/A'; ?></td>
                        <td>
                            <span class="badge bg-info">
                                <?php echo isset($reserv['lama_menginap']) ? $reserv['lama_menginap'] . ' malam' : 'N/A'; ?>
                            </span>
                        </td>
                        <td><strong><?php echo isset($reserv['total_harga']) ? format_rupiah($reserv['total_harga']) : 'N/A'; ?></strong></td>
                        <td>
                            <form method="POST" style="display:inline;" 
                                  onsubmit="return confirm('Proses check-in untuk <?php echo addslashes($reserv['nama_tamu'] ?? 'tamu'); ?>?')">
                                <input type="hidden" name="id_reservasi" value="<?php echo $reserv['id_reservasi']; ?>">
                                <button type="submit" name="checkin" class="btn btn-primary btn-sm">
                                    <i class="bi bi-check-circle me-1"></i>Check-in
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3">Tidak ada reservasi yang siap untuk check-in</p>
            <?php if (!$error): ?>
                <small class="text-muted">
                    Reservasi dengan status 'confirmed' dan tanggal check-in hari ini akan muncul di sini.
                </small>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- tamu checkin -->
<div class="card table-card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Tamu yang Sedang Menginap</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($tamu_checkin)): ?>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Kamar</th>
                        <th>Tamu</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tamu_checkin as $tamu): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($tamu['nomor_kamar'] ?? 'N/A'); ?></strong></td>
                        <td><?php echo htmlspecialchars($tamu['nama_tamu'] ?? 'N/A'); ?></td>
                        <td><?php echo isset($tamu['tgl_checkin']) ? date('d/m/Y', strtotime($tamu['tgl_checkin'])) : 'N/A'; ?></td>
                        <td><?php echo isset($tamu['tgl_checkout']) ? date('d/m/Y', strtotime($tamu['tgl_checkout'])) : 'N/A'; ?></td>
                        <td><?php echo isset($tamu['status_reservasi']) ? status_badge($tamu['status_reservasi']) : 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-4">
            <i class="bi bi-person-x" style="font-size: 2rem; color: #ccc;"></i>
            <p class="text-muted mt-2">Tidak ada tamu yang sedang menginap</p>
        </div>
        <?php endif; ?>
    </div>
</div>