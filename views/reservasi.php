<?php
$message = '';
$message_type = '';

// Handle status update
if (isset($_GET['action']) && isset($_GET['id'])) {
    try {
        $id = sanitize_input($_GET['id']);
        
        if ($_GET['action'] == 'confirm') {
            $stmt = $conn->prepare("UPDATE reservasi SET status_reservasi = 'confirmed' WHERE id_reservasi = ?");
            $stmt->execute([$id]);
            $message = "Reservasi berhasil dikonfirmasi!";
            $message_type = "success";
        } elseif ($_GET['action'] == 'cancel') {
            $stmt = $conn->prepare("UPDATE reservasi SET status_reservasi = 'cancelled' WHERE id_reservasi = ?");
            $stmt->execute([$id]);
            $message = "Reservasi berhasil dibatalkan!";
            $message_type = "success";
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Pagination and filters
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
if ($page_num < 1) $page_num = 1;

$records_per_page = 15;
$offset = ($page_num - 1) * $records_per_page;

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$filter_status = isset($_GET['filter_status']) ? sanitize_input($_GET['filter_status']) : '';

// Initialize variables
$reservasi_list = [];
$total_records = 0;
$total_pages = 0;
$error = null;

$where_clause = "WHERE 1=1";
$params = [];
$param_types = [];

if (!empty($search)) {
    $where_clause .= " AND (nama_tamu ILIKE ? OR nomor_kamar ILIKE ? OR nama_tipe ILIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $param_types = array_merge($param_types, ['string', 'string', 'string']);
}

if (!empty($filter_status)) {
    $where_clause .= " AND status_reservasi = ?";
    $params[] = $filter_status;
    $param_types[] = 'string';
}

try {
    // Count total records
    $count_sql = "SELECT COUNT(*) as total FROM v_reservasi_detail $where_clause";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($params);
    $count_result = $count_stmt->fetch();
    $total_records = $count_result ? (int)$count_result['total'] : 0;
    $total_pages = ceil($total_records / $records_per_page);
    
    // Ensure page number is within valid range
    if ($page_num > $total_pages && $total_pages > 0) {
        $page_num = $total_pages;
        $offset = ($page_num - 1) * $records_per_page;
    }
    
    // Get data with pagination
    $data_params = $params;
    $data_sql = "
        SELECT * FROM v_reservasi_detail 
        $where_clause
        ORDER BY tgl_checkin DESC
        LIMIT $records_per_page OFFSET $offset
    ";
    
    $stmt = $conn->prepare($data_sql);
    $stmt->execute($data_params);
    $reservasi_list = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = $e->getMessage();
    error_log("Database error in reservasi page: " . $e->getMessage());
}
?>

<h1 class="page-title">Data Reservasi</h1>

<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Terjadi kesalahan: <?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Action Buttons -->
<div class="mb-4">
    <a href="?page=reservasi_form" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Buat Reservasi Baru
    </a>
</div>

<!-- Filters -->
<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="reservasi">
            <div class="col-md-5">
                <input type="text" class="form-control search-box" name="search" 
                    placeholder="🔍 Cari nama tamu, nomor kamar, atau tipe..." 
                    value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="filter_status">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $filter_status == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="check-in" <?php echo $filter_status == 'check-in' ? 'selected' : ''; ?>>Check-in</option>
                    <option value="selesai" <?php echo $filter_status == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="cancelled" <?php echo $filter_status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-2"></i>Cari
                </button>
            </div>
            <div class="col-md-2">
                <a href="?page=reservasi" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Daftar Reservasi</h5>
        <span class="badge bg-primary"><?php echo $total_records; ?> Reservasi</span>
    </div>
    <div class="card-body">
        <?php if (!empty($reservasi_list)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tamu</th>
                            <th>Kamar</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Lama</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservasi_list as $res): ?>
                        <tr>
                            <td><strong>#<?php echo htmlspecialchars($res['id_reservasi'] ?? 'N/A'); ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($res['nama_tamu'] ?? 'N/A'); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($res['no_telp'] ?? ''); ?></small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($res['nomor_kamar'] ?? 'N/A'); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($res['nama_tipe'] ?? 'N/A'); ?></small>
                            </td>
                            <td><?php echo isset($res['tgl_checkin']) ? date('d/m/Y', strtotime($res['tgl_checkin'])) : 'N/A'; ?></td>
                            <td><?php echo isset($res['tgl_checkout']) ? date('d/m/Y', strtotime($res['tgl_checkout'])) : 'N/A'; ?></td>
                            <td><?php echo isset($res['lama_menginap']) ? $res['lama_menginap'] . ' malam' : 'N/A'; ?></td>
                            <td><?php echo isset($res['total_harga']) ? format_rupiah($res['total_harga']) : 'N/A'; ?></td>
                            <td><?php echo isset($res['status_reservasi']) ? get_status_badge($res['status_reservasi']) : 'N/A'; ?></td>
                            <td><?php echo isset($res['status_bayar']) ? get_status_badge($res['status_bayar']) : 'N/A'; ?></td>
                            <td>
                                <?php if (($res['status_reservasi'] ?? '') == 'pending'): ?>
                                    <a href="?page=reservasi&action=confirm&id=<?php echo $res['id_reservasi']; ?>" 
                                       class="btn btn-sm btn-success btn-action"
                                       onclick="return confirm('Konfirmasi reservasi ini?')">
                                        <i class="bi bi-check"></i> Confirm
                                    </a>
                                    <a href="?page=reservasi&action=cancel&id=<?php echo $res['id_reservasi']; ?>" 
                                       class="btn btn-sm btn-danger btn-action"
                                       onclick="return confirm('Batalkan reservasi ini?')">
                                        <i class="bi bi-x"></i> Cancel
                                    </a>
                                <?php elseif (($res['status_reservasi'] ?? '') == 'confirmed'): ?>
                                    <span class="text-success">
                                        <i class="bi bi-check-circle"></i> Terkonfirmasi
                                    </span>
                                <?php elseif (($res['status_reservasi'] ?? '') == 'check-in'): ?>
                                    <span class="text-primary">
                                        <i class="bi bi-person-check"></i> Sedang Menginap
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page_num <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=reservasi&page_num=<?php echo $page_num - 1; ?>&search=<?php echo urlencode($search); ?>&filter_status=<?php echo urlencode($filter_status); ?>">
                            Previous
                        </a>
                    </li>
                    
                    <?php 
                    $start_page = max(1, $page_num - 2);
                    $end_page = min($total_pages, $start_page + 4);
                    $start_page = max(1, $end_page - 4);
                    
                    for ($i = $start_page; $i <= $end_page; $i++): 
                    ?>
                    <li class="page-item <?php echo $page_num == $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=reservasi&page_num=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter_status=<?php echo urlencode($filter_status); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $page_num >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=reservasi&page_num=<?php echo $page_num + 1; ?>&search=<?php echo urlencode($search); ?>&filter_status=<?php echo urlencode($filter_status); ?>">
                            Next
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">Tidak ada data reservasi</p>
                <?php if (!empty($search) || !empty($filter_status)): ?>
                    <p class="text-muted">Coba ubah filter pencarian Anda</p>
                    <a href="?page=reservasi" class="btn btn-primary mt-2">
                        <i class="bi bi-arrow-clockwise me-2"></i>Tampilkan Semua
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>