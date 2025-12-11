<?php
$message = '';
$message_type = '';

$tamu_checkin = [];
$error = null;

//checkout dgn store procedure
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    try {
        $conn->beginTransaction();
        
        $id_reservasi = sanitize_input($_POST['id_reservasi']);
        
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

//tamu checkout
try {
    $stmt = $conn->query("
        SELECT * FROM v_reservasi_detail 
        WHERE status_reservasi = 'check-in'
        ORDER BY tgl_checkout
    ");
    $tamu_checkin = $stmt->fetchAll();
    
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
        <?php if (!empty($tamu_checkin)): ?>
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
                    <?php foreach ($tamu_checkin as $tamu): ?>
                    <?php
                    $hari_ini = date('Y-m-d');
                    $tgl_checkout = $tamu['tgl_checkout'] ?? '';
                    $jatuh_tempo = (!empty($tgl_checkout) && $tgl_checkout <= $hari_ini);
                    $baris_tabel = $jatuh_tempo ? 'table-warning' : '';
                    ?>
                    <tr class="<?php echo $baris_tabel; ?>">
                        <td><strong>#<?php echo htmlspecialchars($tamu['id_reservasi'] ?? 'N/A'); ?></strong></td>
                        <td>
                            <?php echo htmlspecialchars($tamu['nama_tamu'] ?? 'N/A'); ?><br>
                            <?php if (!empty($tamu['no_telp'])): ?>
                                <small class="text-muted"><?php echo htmlspecialchars($tamu['no_telp']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($tamu['nomor_kamar'] ?? 'N/A'); ?></strong><br>
                            <small><?php echo htmlspecialchars($tamu['nama_tipe'] ?? 'N/A'); ?></small>
                        </td>
                        <td><?php echo isset($tamu['tgl_checkin']) ? format_tanggal($tamu['tgl_checkin']) : 'N/A'; ?></td>
                        <td>
                            <?php echo isset($tamu['tgl_checkout']) ? format_tanggal($tamu['tgl_checkout']) : 'N/A'; ?>
                            <?php if ($jatuh_tempo): ?>
                                <br><span class="badge bg-warning">Jatuh Tempo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <?php echo isset($tamu['lama_menginap']) ? $tamu['lama_menginap'] . ' malam' : 'N/A'; ?>
                            </span>
                        </td>
                        <td>
                            <strong><?php echo isset($tamu['total_harga']) ? format_rupiah($tamu['total_harga']) : 'N/A'; ?></strong>
                        </td>
                        <td><?php echo isset($tamu['status_bayar']) ? status_badge($tamu['status_bayar']) : 'N/A'; ?></td>
                        <td>
                            <form method="POST" style="display:inline;" 
                                  onsubmit="return confirm('Proses check-out untuk <?php echo addslashes($tamu['nama_tamu'] ?? 'tamu'); ?>?\n\nCatatan: Pembayaran akan otomatis menjadi lunas.')">
                                <input type="hidden" name="id_reservasi" value="<?php echo $tamu['id_reservasi']; ?>">
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