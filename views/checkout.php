<?php
$message = '';
$message_type = '';

// Initialize variables
$checkin_guests = [];
$error = null;

// Handle Check-out Process using Stored Procedure
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    try {
        $conn->beginTransaction();
        
        $id_reservasi = sanitize_input($_POST['id_reservasi']);
        
        // Call stored procedure for check-out
        $stmt = $conn->prepare("CALL proses_checkout(?)");
        $stmt->execute([$id_reservasi]);
        
        $conn->commit();
        
        $message = "Check-out berhasil! Terima kasih atas kunjungannya.";
        $message_type = "success";
        
    } catch (Exception $e) {
        $conn->rollBack();
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Get check-in guests for check-out
try {
    $stmt = $conn->query("
        SELECT * FROM v_reservasi_detail 
        WHERE status_reservasi = 'check-in'
        ORDER BY tgl_checkout
    ");
    $checkin_guests = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error mengambil data tamu: " . $e->getMessage();
    error_log($error);
}
?>

<h1 class="page-title">Check-out Tamu</h1>

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
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0"><i class="bi bi-box-arrow-right me-2"></i>Daftar Tamu untuk Check-out</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($checkin_guests)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tamu</th>
                        <th>Kamar</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Lama Menginap</th>
                        <th>Total Tagihan</th>
                        <th>Status Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($checkin_guests as $guest): ?>
                    <?php
                    $today = date('Y-m-d');
                    $checkout_date = $guest['tgl_checkout'] ?? '';
                    $is_due = (!empty($checkout_date) && $checkout_date <= $today);
                    $row_class = $is_due ? 'table-warning' : '';
                    ?>
                    <tr class="<?php echo $row_class; ?>">
                        <td><strong>#<?php echo htmlspecialchars($guest['id_reservasi'] ?? 'N/A'); ?></strong></td>
                        <td>
                            <?php echo htmlspecialchars($guest['nama_tamu'] ?? 'N/A'); ?><br>
                            <?php if (!empty($guest['no_telp'])): ?>
                                <small class="text-muted"><?php echo htmlspecialchars($guest['no_telp']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($guest['nomor_kamar'] ?? 'N/A'); ?></strong><br>
                            <small><?php echo htmlspecialchars($guest['nama_tipe'] ?? 'N/A'); ?></small>
                        </td>
                        <td><?php echo isset($guest['tgl_checkin']) ? format_tanggal($guest['tgl_checkin']) : 'N/A'; ?></td>
                        <td>
                            <?php echo isset($guest['tgl_checkout']) ? format_tanggal($guest['tgl_checkout']) : 'N/A'; ?>
                            <?php if ($is_due): ?>
                                <br><span class="badge bg-warning">Jatuh Tempo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <?php echo isset($guest['lama_menginap']) ? $guest['lama_menginap'] . ' malam' : 'N/A'; ?>
                            </span>
                        </td>
                        <td>
                            <strong><?php echo isset($guest['total_harga']) ? format_rupiah($guest['total_harga']) : 'N/A'; ?></strong>
                        </td>
                        <td><?php echo isset($guest['status_bayar']) ? get_status_badge($guest['status_bayar']) : 'N/A'; ?></td>
                        <td>
                            <form method="POST" style="display:inline;" 
                                  onsubmit="return confirm('Proses check-out untuk <?php echo addslashes($guest['nama_tamu'] ?? 'tamu'); ?>?\n\nCatatan: Pembayaran akan otomatis menjadi lunas.')">
                                <input type="hidden" name="id_reservasi" value="<?php echo $guest['id_reservasi']; ?>">
                                <button type="submit" name="checkout" class="btn btn-danger btn-sm">
                                    <i class="bi bi-door-open me-1"></i>Check-out
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
            <p class="text-muted mt-3">Tidak ada tamu yang sedang menginap</p>
            <?php if (!$error): ?>
                <small class="text-muted">
                    Tamu dengan status 'check-in' akan muncul di sini untuk proses check-out.
                </small>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>