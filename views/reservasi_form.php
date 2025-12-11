<?php
$message = '';
$message_type = '';

//proses form 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buat_reservasi'])) {
    try {
        $conn->beginTransaction();

        $id_tamu = sanitize_input($_POST['id_tamu']);
        $id_kamar = sanitize_input($_POST['id_kamar']);
        $tgl_checkin = sanitize_input($_POST['tgl_checkin']);
        $tgl_checkout = sanitize_input($_POST['tgl_checkout']);
        $jumlah_tamu = sanitize_input($_POST['jumlah_tamu']);
        
        $stmt = $conn->prepare("
            INSERT INTO reservasi (id_tamu, id_kamar, tgl_checkin, tgl_checkout, jumlah_tamu, status_reservasi, tgl_reservasi)
            VALUES (?, ?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP)
            RETURNING id_reservasi
        ");
        $stmt->execute([$id_tamu, $id_kamar, $tgl_checkin, $tgl_checkout, $jumlah_tamu]);
        $id_reservasi = $stmt->fetch()['id_reservasi'];
        
        //hitung total
        $total_stmt = $conn->prepare("SELECT hitung_total_reservasi(?) as total");
        $total_stmt->execute([$id_reservasi]);
        $total = $total_stmt->fetch()['total'];
        
        $stmt = $conn->prepare("
            INSERT INTO pembayaran (id_reservasi, jumlah, status_bayar)
            VALUES (?, ?, 'belum lunas')
        ");
        $stmt->execute([$id_reservasi, $total]);
        
        $conn->commit();
        
        $message = "Reservasi berhasil dibuat! ID Reservasi: #$id_reservasi, Total: " . format_rupiah($total);
        $message_type = "success";
        
    } catch (Exception $e) {
        $conn->rollBack();
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

try {
    //kamar
    $kamar_tersedia = $conn->query("SELECT * FROM v_kamar_tersedia ORDER BY nama_tipe, nomor_kamar")->fetchAll();
    
    //tamu
    $tamu = $conn->query("SELECT * FROM tamu ORDER BY nama_tamu")->fetchAll();
    
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>

<h1 class="page-title">Buat Reservasi Baru</h1>

<div class="btn-group btn-group-sm mb-3">
    <a href="?page=dashboard" class="btn btn-light">Home</a>
    <a href="?page=reservasi" class="btn btn-light">Reservasi</a>
    <button class="btn btn-secondary" disabled>Buat Reservasi</button>
</div>


<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card table-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Form Reservasi</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="reservasiForm">
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-person-circle me-2"></i>Data Tamu
                        </h6>
                        
                        <div class="mb-3">
                            <label class="form-label">Pilih Tamu</label>
                            <select class="form-select" name="id_tamu" id="id_tamu"
                            >
                                <option value="">-- Pilih Tamu --</option>
                                <?php foreach ($tamu as $tamu): ?>
                                <option value="<?php echo $tamu['id_tamu']; ?>">
                                    <?php echo $tamu['nama_tamu']; ?> - <?php echo $tamu['no_telp']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Atau <a href="?page=tamu" target="_blank">tambah tamu baru</a></small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Jumlah Tamu</label>
                            <input type="number" class="form-control" name="jumlah_tamu" id="jumlah_tamu" 
                                min="1" max="10" value="1">
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-door-open me-2"></i>Pilih Kamar
                        </h6>
                        
                        <div class="mb-3">
                            <label class="form-label">Kamar</label>
                            <select class="form-select" name="id_kamar" id="id_kamar" onchange="updateInfoKamar()">
                                <option value="">-- Pilih Kamar --</option>
                                <?php foreach ($kamar_tersedia as $kamar): ?>
                                <option value="<?php echo $kamar['id_kamar']; ?>" 
                                    data-tipe="<?php echo $kamar['nama_tipe']; ?>"
                                    data-harga="<?php echo $kamar['harga_per_malam']; ?>"
                                    data-kapasitas="<?php echo $kamar['kapasitas']; ?>">
                                    <?php echo $kamar['nomor_kamar']; ?> - <?php echo $kamar['nama_tipe']; ?> 
                                    (<?php echo format_rupiah($kamar['harga_per_malam']); ?>/malam)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div id="roomInfo" class="alert alert-info d-none">
                            <strong>Info Kamar:</strong><br>
                            <span id="infoTipe"></span><br>
                            <span id="infoHarga"></span><br>
                            <span id="infoKapasitas"></span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-calendar-range me-2"></i>Tanggal Menginap
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Check-in</label>
                                    <input type="date" class="form-control" name="tgl_checkin" id="tgl_checkin" 
                                        min="<?php echo date('Y-m-d'); ?>" onchange="hitungTotal()">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Check-out</label>
                                    <input type="date" class="form-control" name="tgl_checkout" id="tgl_checkout" 
                                        min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" onchange="hitungTotal()">
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Lama Menginap:</strong> <span id="lamaMenginap">-</span> malam
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-end">
                        <a href="?page=reservasi" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Batal
                        </a>
                        <button type="submit" name="buat_reservasi" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Buat Reservasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card table-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Ringkasan Pembayaran</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Harga per Malam:</span>
                        <strong id="displayHargaMalam">Rp 0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Jumlah Malam:</span>
                        <strong id="displayJumlahMalam">0</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <h5>Total Pembayaran:</h5>
                        <h5 class="text-primary" id="displayTotal">Rp 0</h5>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        Status pembayaran awal: <strong>Belum Lunas</strong>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateInfoKamar() {
    const select = document.getElementById('id_kamar');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        const tipe = option.getAttribute('data-tipe');
        const harga = parseFloat(option.getAttribute('data-harga'));
        const kapasitas = option.getAttribute('data-kapasitas');
        
        document.getElementById('infoTipe').textContent = 'Tipe: ' + tipe;
        document.getElementById('infoHarga').textContent = 'Harga: ' + formatRupiah(harga);
        document.getElementById('infoKapasitas').textContent = 'Kapasitas: ' + kapasitas + ' orang';
        document.getElementById('roomInfo').classList.remove('d-none');
        
        hitungTotal();
    } else {
        document.getElementById('roomInfo').classList.add('d-none');
    }
}

function hitungTotal() {
    const select = document.getElementById('id_kamar');
    const option = select.options[select.selectedIndex];
    const checkin = document.getElementById('tgl_checkin').value;
    const checkout = document.getElementById('tgl_checkout').value;
    
    if (option.value && checkin && checkout) {
        const harga = parseFloat(option.getAttribute('data-harga'));
        const date1 = new Date(checkin);
        const date2 = new Date(checkout);
        const diffTime = Math.abs(date2 - date1);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays > 0) {
            const total = harga * diffDays;
            
            document.getElementById('lamaMenginap').textContent = diffDays;
            document.getElementById('displayHargaMalam').textContent = formatRupiah(harga);
            document.getElementById('displayJumlahMalam').textContent = diffDays;
            document.getElementById('displayTotal').textContent = formatRupiah(total);
        }
    }
}
</script>