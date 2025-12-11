<?php
$message = '';
$message_type = '';

//update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bayar'])) {
    try {
        $id_pembayaran = sanitize_input($_POST['id_pembayaran']);
        $metode = sanitize_input($_POST['metode_bayar']);
        
        $stmt = $conn->prepare("
            UPDATE pembayaran 
            SET metode_bayar = ?, 
                tgl_bayar = CURRENT_TIMESTAMP, 
                status_bayar = 'lunas' 
            WHERE id_pembayaran = ?
        ");
        $stmt->execute([$metode, $id_pembayaran]);
        
        $message = "Pembayaran berhasil dicatat!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

//read
$filter = isset($_GET['filter']) ? sanitize_input($_GET['filter']) : '';
$where = $filter ? "WHERE p.status_bayar = ?" : "";
$params = $filter ? [$filter] : [];

try {
    $stmt = $conn->prepare("
        SELECT 
            p.*,
            r.id_reservasi,
            t.nama_tamu,
            k.nomor_kamar,
            r.tgl_checkin,
            r.tgl_checkout
        FROM pembayaran p
        JOIN reservasi r ON p.id_reservasi = r.id_reservasi
        JOIN tamu t ON r.id_tamu = t.id_tamu
        JOIN kamar k ON r.id_kamar = k.id_kamar
        $where
        ORDER BY p.status_bayar DESC NULLS LAST, p.id_pembayaran DESC
        LIMIT 50
    ");
    $stmt->execute($params);
    $pembayaran_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>

<h1 class="page-title">Data Pembayaran</h1>

<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filter -->
<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="pembayaran">
            <div class="col-md-3">
                <select class="form-select" name="filter" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="lunas" <?php echo $filter == 'lunas' ? 'selected' : ''; ?>>Lunas</option>
                    <option value="belum lunas" <?php echo $filter == 'belum lunas' ? 'selected' : ''; ?>>Belum Lunas</option>
                </select>
            </div>
            <div class="col-md-2">
                <a href="?page=pembayaran" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- tabel -->
<div class="card table-card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Daftar Pembayaran</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tamu</th>
                        <th>Kamar</th>
                        <th>Check-in/out</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                        <th>Tanggal Bayar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pembayaran_list as $bayar): ?>
                    <tr>
                        <td>#<?php echo $bayar['id_pembayaran']; ?></td>
                        <td><?php echo htmlspecialchars($bayar['nama_tamu']); ?></td>
                        <td><?php echo $bayar['nomor_kamar']; ?></td>
                        <td>
                            <?php echo date('d/m', strtotime($bayar['tgl_checkin'])); ?> - 
                            <?php echo date('d/m', strtotime($bayar['tgl_checkout'])); ?>
                        </td>
                        <td><strong><?php echo format_rupiah($bayar['jumlah']); ?></strong></td>
                        <td><?php echo $bayar['metode_bayar'] ?: '-'; ?></td>
                        <td><?php echo $bayar['tgl_bayar'] ? date('d/m/Y H:i', strtotime($bayar['tgl_bayar'])) : '-'; ?></td>
                        <td><?php echo status_badge($bayar['status_bayar']); ?></td>
                        <td>
                            <?php if ($bayar['status_bayar'] == 'belum lunas'): ?>
                            <button type="button" class="btn btn-sm btn-success" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#bayarModal<?php echo $bayar['id_pembayaran']; ?>">
                                <i class="bi bi-cash"></i> Bayar
                            </button>
                            
                            <!-- modal -->
                            <div class="modal fade" id="bayarModal<?php echo $bayar['id_pembayaran']; ?>">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Proses Pembayaran</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <p><strong>Tamu:</strong> <?php echo $bayar['nama_tamu']; ?></p>
                                                <p><strong>Total:</strong> <?php echo format_rupiah($bayar['jumlah']); ?></p>
                                                <input type="hidden" name="id_pembayaran" value="<?php echo $bayar['id_pembayaran']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Metode Pembayaran</label>
                                                    <select class="form-select" name="metode_bayar" required>
                                                        <option value="">Pilih Metode</option>
                                                        <option value="cash">Cash</option>
                                                        <option value="transfer">Transfer Bank</option>
                                                        <option value="kartu kredit">Kartu Kredit</option>
                                                        <option value="kartu debit">Kartu Debit</option>
                                                        <option value="e-wallet">E-Wallet</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="bayar" class="btn btn-success">Konfirmasi Pembayaran</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <span class="text-success"><i class="bi bi-check-circle"></i> Lunas</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>