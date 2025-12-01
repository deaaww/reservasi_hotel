<?php
$message = '';
$message_type = '';

// Initialize variables
$confirmed_reservations = [];
$checkin_guests = [];
$error = null;

// Handle Check-in Process using Stored Procedure
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkin'])) {
    try {
        $conn->beginTransaction();
        
        $id_reservasi = sanitize_input($_POST['id_reservasi']);
        
        // Call stored procedure for check-in
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

// Get confirmed reservations for check-in
try {
    $stmt = $conn->query("
        SELECT * FROM v_reservasi_detail 
        WHERE status_reservasi = 'confirmed' 
        AND tgl_checkin <= CURRENT_DATE
        ORDER BY tgl_checkin
    ");
    $confirmed_reservations = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error mengambil data reservasi: " . $e->getMessage();
    error_log($error);
}

// Get Already Checked-in Guests
try {
    $stmt = $conn->query("
        SELECT * FROM v_reservasi_detail 
        WHERE status_reservasi = 'check-in' 
        ORDER BY tgl_checkin DESC
        LIMIT 10
    ");
    $checkin_guests = $stmt->fetchAll();
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
        <?php if (!empty($confirmed_reservations)): ?>
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
                    <?php foreach ($confirmed_reservations as $res): ?>
                    <tr>
                        <td><strong>#<?php echo htmlspecialchars($res['id_reservasi'] ?? 'N/A'); ?></strong></td>
                        <td><?php echo htmlspecialchars($res['nama_tamu'] ?? 'N/A'); ?></td>
                        <td>
                            <?php if (!empty($res['no_telp'])): ?>
                                <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($res['no_telp']); ?><br>
                            <?php endif; ?>
                            <?php if (!empty($res['email'])): ?>
                                <small class="text-muted">
                                    <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($res['email']); ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($res['nomor_kamar'] ?? 'N/A'); ?></strong><br>
                            <small><?php echo htmlspecialchars($res['nama_tipe'] ?? 'N/A'); ?></small>
                        </td>
                        <td><?php echo isset($res['tgl_checkin']) ? format_tanggal($res['tgl_checkin']) : 'N/A'; ?></td>
                        <td><?php echo isset($res['tgl_checkout']) ? format_tanggal($res['tgl_checkout']) : 'N/A'; ?></td>
                        <td>
                            <span class="badge bg-info">
                                <?php echo isset($res['lama_menginap']) ? $res['lama_menginap'] . ' malam' : 'N/A'; ?>
                            </span>
                        </td>
                        <td><strong><?php echo isset($res['total_harga']) ? format_rupiah($res['total_harga']) : 'N/A'; ?></strong></td>
                        <td>
                            <form method="POST" style="display:inline;" 
                                  onsubmit="return confirm('Proses check-in untuk <?php echo addslashes($res['nama_tamu'] ?? 'tamu'); ?>?')">
                                <input type="hidden" name="id_reservasi" value="<?php echo $res['id_reservasi']; ?>">
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

<!-- Already Checked-in Guests -->
<div class="card table-card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Tamu yang Sedang Menginap</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($checkin_guests)): ?>
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
                    <?php foreach ($checkin_guests as $guest): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($guest['nomor_kamar'] ?? 'N/A'); ?></strong></td>
                        <td><?php echo htmlspecialchars($guest['nama_tamu'] ?? 'N/A'); ?></td>
                        <td><?php echo isset($guest['tgl_checkin']) ? date('d/m/Y', strtotime($guest['tgl_checkin'])) : 'N/A'; ?></td>
                        <td><?php echo isset($guest['tgl_checkout']) ? date('d/m/Y', strtotime($guest['tgl_checkout'])) : 'N/A'; ?></td>
                        <td><?php echo isset($guest['status_reservasi']) ? get_status_badge($guest['status_reservasi']) : 'N/A'; ?></td>
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