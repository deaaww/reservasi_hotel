<?php
// Initialize variables
$laporan_okupansi = [];
$top_guests = [];
$revenue_by_type = [];
$error = null;

// VARIABEL TAMBAHAN UNTUK SUMMARY CARD LUNAS
$total_lunas_bulan_ini = 0; 

// Get laporan okupansi dari view
try {
    // 1. Ambil data laporan okupansi bulanan
    $stmt = $conn->query("
        SELECT * FROM v_laporan_okupansi 
        ORDER BY bulan DESC
        LIMIT 12
    ");
    $laporan_okupansi = $stmt->fetchAll();
    
    // 2. Ambil total pendapatan berdasarkan pembayaran LUNAS bulan ini
    // Logika: Jumlahkan semua kolom jumlah_bayar dari tabel pembayaran yang lunas di bulan ini (NOW())
    $lunas_stmt = $conn->query("
        SELECT 
            SUM(p.jumlah) 
        FROM pembayaran p
        WHERE p.status_bayar = 'lunas'
        AND date_trunc('month', p.tgl_bayar) = date_trunc('month', NOW());
    ");
    // Gunakan fetchColumn() karena hanya mengambil 1 nilai
    $total_lunas_bulan_ini = $lunas_stmt->fetchColumn() ?? 0;
    
    // 3. Get top guests
    $top_guests_stmt = $conn->query("
        SELECT 
            t.nama_tamu,
            t.no_telp,
            t.email,
            COUNT(r.id_reservasi) as total_reservasi,
            SUM(r.tgl_checkout - r.tgl_checkin) as total_malam,
            SUM(tk.harga_per_malam * (r.tgl_checkout - r.tgl_checkin)) as total_spending
        FROM tamu t
        JOIN reservasi r ON t.id_tamu = r.id_tamu
        JOIN kamar k ON r.id_kamar = k.id_kamar
        JOIN tipe_kamar tk ON k.id_tipe = tk.id_tipe
        WHERE r.status_reservasi IN ('check-in', 'selesai')
        GROUP BY t.id_tamu, t.nama_tamu, t.no_telp, t.email
        HAVING COUNT(r.id_reservasi) > 1
        ORDER BY total_reservasi DESC
    ");
    $top_guests = $top_guests_stmt->fetchAll();
    
    // 4. Get revenue by room type
    $revenue_by_type_stmt = $conn->query("
        SELECT 
            tk.nama_tipe,
            COUNT(r.id_reservasi) as total_booking,
            SUM(r.tgl_checkout - r.tgl_checkin) as total_malam,
            SUM(tk.harga_per_malam * (r.tgl_checkout - r.tgl_checkin)) as total_revenue
        FROM tipe_kamar tk
        LEFT JOIN kamar k ON tk.id_tipe = k.id_tipe
        LEFT JOIN reservasi r ON k.id_kamar = r.id_kamar
        WHERE r.status_reservasi IN ('check-in', 'selesai')
        GROUP BY tk.id_tipe, tk.nama_tipe
        ORDER BY total_revenue DESC
    ");
    $revenue_by_type = $revenue_by_type_stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error mengambil data laporan: " . $e->getMessage();
    error_log($error);
}
?>

<h1 class="page-title">Laporan & Statistik</h1>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <?php
    $total_revenue = $total_lunas_bulan_ini; // MENGAMBIL TOTAL LUNAS SEKARANG
    $total_bookings = 0;
    $avg_okupansi = 0;
    
    // Gunakan Laporan Okupansi untuk Total Booking dan Okupansi (data yang tidak janggal)
    if (!empty($laporan_okupansi)) {
        $current_month = $laporan_okupansi[0];
        $total_bookings = $current_month['total_reservasi'] ?? 0;
        $avg_okupansi = $current_month['tingkat_okupansi'] ?? 0;
    }
    ?>
    
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Pendapatan Bulan Ini (Lunas)</h6>
                <h3 class="mb-0 text-success"><?php echo format_rupiah($total_revenue); ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Booking Bulan Ini (Okupansi)</h6>
                <h3 class="mb-0 text-primary"><?php echo $total_bookings; ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Tingkat Okupansi Bulan Ini</h6>
                <h3 class="mb-0 text-info"><?php echo number_format($avg_okupansi, 2); ?>%</h3>
            </div>
        </div>
    </div>
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Laporan Okupansi per Bulan (Berdasarkan Check-in)</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($laporan_okupansi)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Total Reservasi</th>
                            <th>Kamar Terpakai</th>
                            <th>Total Kamar</th>
                            <th>Tingkat Okupansi</th>
                            <th>Total Pendapatan (Okupansi)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laporan_okupansi as $lap): ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php 
                                    $bulan_indo = array(
                                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                        '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                    );

                                    $dt = new DateTime($lap['bulan'], new DateTimeZone('Asia/Jakarta'));
                                    $bulan = $dt->format('m');
                                    $tahun = $dt->format('Y');

                                    echo $bulan_indo[$bulan] . ' ' . $tahun;
                                    ?>
                                </strong>
                            </td>
                            <td><?php echo $lap['total_reservasi'] ?? 0; ?></td>
                            <td><?php echo $lap['kamar_terpakai'] ?? 0; ?></td>
                            <td><?php echo $lap['total_kamar'] ?? 0; ?></td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                            style="width: <?php echo min($lap['tingkat_okupansi'] ?? 0, 100); ?>%">
                                        <?php echo number_format($lap['tingkat_okupansi'] ?? 0, 2); ?>%
                                    </div>
                                </div>
                            </td>
                            <td><strong><?php echo format_rupiah($lap['total_pendapatan'] ?? 0); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th>TOTAL</th>
                            <th><?php echo array_sum(array_column($laporan_okupansi, 'total_reservasi')); ?></th>
                            <th colspan="2"></th>
                            <th>
                                Rata-rata: 
                                <?php 
                                $total_okupansi = array_sum(array_column($laporan_okupansi, 'tingkat_okupansi'));
                                $avg_okupansi = count($laporan_okupansi) > 0 ? $total_okupansi / count($laporan_okupansi) : 0;
                                echo number_format($avg_okupansi, 2); 
                                ?>%
                            </th>
                            <th><?php echo format_rupiah(array_sum(array_column($laporan_okupansi, 'total_pendapatan'))); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">Tidak ada data laporan okupansi</p>
                <?php if (!$error): ?>
                    <small class="text-muted">
                        Data laporan akan muncul setelah ada reservasi yang selesai.
                    </small>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card table-card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Tamu Paling Sering Menginap</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($top_guests)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Tamu</th>
                                    <th>Total Booking</th>
                                    <th>Total Malam</th>
                                    <th>Total Spending</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($top_guests as $guest): 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($guest['nama_tamu'] ?? 'N/A'); ?></strong><br>
                                        <?php if (!empty($guest['no_telp'])): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($guest['no_telp']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-primary"><?php echo $guest['total_reservasi'] ?? 0; ?>x</span></td>
                                    <td><?php echo $guest['total_malam'] ?? 0; ?> malam</td>
                                    <td><strong><?php echo format_rupiah($guest['total_spending'] ?? 0); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-people" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Tidak ada data tamu</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card table-card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Pendapatan per Tipe Kamar (Okupansi)</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($revenue_by_type)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tipe Kamar</th>
                                    <th>Total Booking</th>
                                    <th>Total Malam</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($revenue_by_type as $rev): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($rev['nama_tipe'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo $rev['total_booking'] ?? 0; ?></td>
                                    <td><?php echo $rev['total_malam'] ?? 0; ?></td>
                                    <td><strong><?php echo format_rupiah($rev['total_revenue'] ?? 0); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th>TOTAL</th>
                                    <th><?php echo array_sum(array_column($revenue_by_type, 'total_booking')); ?></th>
                                    <th><?php echo array_sum(array_column($revenue_by_type, 'total_malam')); ?></th>
                                    <th><?php echo format_rupiah(array_sum(array_column($revenue_by_type, 'total_revenue'))); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-bar-chart" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Tidak ada data pendapatan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card table-card">
    <div class="card-body text-center">
        <h5 class="mb-3">Export Laporan</h5>
        <button class="btn btn-success me-2" onclick="window.print()">
            <i class="bi bi-printer me-2"></i>Print Laporan
        </button>
        <button class="btn btn-primary" onclick="exportToCSV()">
            <i class="bi bi-download me-2"></i>Export to CSV
        </button>
    </div>
</div>

<script>
function exportToCSV() {
    alert('Fitur export CSV akan segera tersedia!');
    // Implementasi export CSV
}
</script>