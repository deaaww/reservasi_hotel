<?php
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$message_type = '';

//create
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    try {
        $id_tipe = sanitize_input($_POST['id_tipe']);
        $nomor_kamar = sanitize_input($_POST['nomor_kamar']);
        $lantai = sanitize_input($_POST['lantai']);
        $status = sanitize_input($_POST['status']);
        
        $stmt = $conn->prepare("
            INSERT INTO kamar (id_tipe, nomor_kamar, lantai, status) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$id_tipe, $nomor_kamar, $lantai, $status]);
        
        $message = "Kamar berhasil ditambahkan!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

//update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    try {
        $id_kamar = sanitize_input($_POST['id_kamar']);
        $id_tipe = sanitize_input($_POST['id_tipe']);
        $nomor_kamar = sanitize_input($_POST['nomor_kamar']);
        $lantai = sanitize_input($_POST['lantai']);
        $status = sanitize_input($_POST['status']);
        
        $stmt = $conn->prepare("
            UPDATE kamar 
            SET id_tipe = ?, nomor_kamar = ?, lantai = ?, status = ? 
            WHERE id_kamar = ?
        ");
        $stmt->execute([$id_tipe, $nomor_kamar, $lantai, $status, $id_kamar]);
        
        $message = "Kamar berhasil diupdate!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

//delete
if ($action == 'delete' && isset($_GET['id'])) {
    try {
        $id = sanitize_input($_GET['id']);
        $stmt = $conn->prepare("DELETE FROM kamar WHERE id_kamar = ?");
        $stmt->execute([$id]);
        
        $message = "Kamar berhasil dihapus!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

//read
$no_hal = isset($_GET['no_hal']) ? (int)$_GET['no_hal'] : 1;
$data_per_hal = 10;
$offset = ($no_hal - 1) * $data_per_hal;

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$filter_status = isset($_GET['filter_status']) ? sanitize_input($_GET['filter_status']) : '';

$where_clause = "WHERE 1=1";
$params = [];

if ($search != '') {
    $where_clause .= " AND (k.nomor_kamar ILIKE ? OR tk.nama_tipe ILIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_status != '') {
    $where_clause .= " AND k.status = ?";
    $params[] = $filter_status;
}

try {
    //hitung total data
    $count_stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM kamar k 
        JOIN tipe_kamar tk ON k.id_tipe = tk.id_tipe 
        $where_clause
    ");
    $count_stmt->execute($params);
    $total_data = $count_stmt->fetch()['total'];
    $total_hal = ceil($total_data / $data_per_hal);
    
    //ambil data sesuai halaman
    $params[] = $data_per_hal;
    $params[] = $offset;
    
    $stmt = $conn->prepare("
        SELECT k.*, tk.nama_tipe, tk.harga_per_malam, tk.kapasitas
        FROM kamar k
        JOIN tipe_kamar tk ON k.id_tipe = tk.id_tipe
        $where_clause
        ORDER BY k.nomor_kamar
        LIMIT ? offset ?
    ");
    $stmt->execute($params);
    $kamar_list = $stmt->fetchAll();
    
    //daftar tipe kamar dropdown
    $tipe_stmt = $conn->query("SELECT * FROM tipe_kamar ORDER BY nama_tipe");
    $tipe_kamar_list = $tipe_stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = $e->getMessage();
}

//data kamar untuk edit
$edit_data = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM kamar WHERE id_kamar = ?");
    $stmt->execute([sanitize_input($_GET['id'])]);
    $edit_data = $stmt->fetch();
}
?>

<h1 class="page-title">Data Kamar</h1>

<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- form -->
<div class="card table-card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-<?php echo $edit_data ? 'pencil' : 'plus-circle'; ?> me-2"></i>
            <?php echo $edit_data ? 'Edit Kamar' : 'Tambah Kamar Baru'; ?>
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" novalidate>
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Tipe Kamar</label>
                        <select class="form-select" name="id_tipe" id="id_tipe" required>
                            <option value="">Pilih Tipe Kamar</option>
                            <?php foreach ($tipe_kamar_list as $tipe): ?>
                            <option value="<?php echo $tipe['id_tipe']; ?>" 
                                <?php echo ($edit_data && $edit_data['id_tipe'] == $tipe['id_tipe']) ? 'selected' : ''; ?>>
                                <?php echo $tipe['nama_tipe']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Nomor Kamar</label>
                        <input type="text" class="form-control" name="nomor_kamar" id="nomor_kamar"
                            value="<?php echo $edit_data ? $edit_data['nomor_kamar'] : ''; ?>" 
                            placeholder="Contoh: 201" required>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Lantai</label>
                        <input type="number" class="form-control" name="lantai" id="lantai"
                            value="<?php echo $edit_data ? $edit_data['lantai'] : ''; ?>" 
                            min="1" max="20" required>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="status" required>
                            <option value="tersedia" <?php echo ($edit_data && $edit_data['status'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                            <option value="terisi" <?php echo ($edit_data && $edit_data['status'] == 'terisi') ? 'selected' : ''; ?>>Terisi</option>
                            <option value="maintenance" <?php echo ($edit_data && $edit_data['status'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <?php if ($edit_data): ?>
                <input type="hidden" name="id_kamar" value="<?php echo $edit_data['id_kamar']; ?>">
                <button type="submit" name="update" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Update Kamar
                </button>
                <a href="?page=kamar" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-2"></i>Batal
                </a>
            <?php else: ?>
                <button type="submit" name="tambah" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Simpan Kamar
                </button>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- filter & search -->
<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="kamar">
            <div class="col-md-4">
                <input type="text" class="form-control search-box" name="search" 
                    placeholder="🔍 Cari nomor kamar atau tipe..." 
                    value="<?php echo $search; ?>"
                    onkeyup="this.form.submit()">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="filter_status" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="tersedia" <?php echo $filter_status == 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                    <option value="terisi" <?php echo $filter_status == 'terisi' ? 'selected' : ''; ?>>Terisi</option>
                    <option value="maintenance" <?php echo $filter_status == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                </select>
            </div>
            <div class="col-md-2">
                <a href="?page=kamar" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- tabel data kamar -->
<div class="card table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Daftar Kamar</h5>
        <span class="badge bg-primary"><?php echo $total_data; ?> Kamar</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="kamarTable">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Nomor Kamar</th>
                        <th>Tipe Kamar</th>
                        <th>Lantai</th>
                        <th>Harga/Malam</th>
                        <th>Kapasitas</th>
                        <th>Status</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($kamar_list) > 0): ?>
                        <?php foreach ($kamar_list as $kamar): ?>
                        <tr>
                            <td><?php echo $kamar['id_kamar']; ?></td>
                            <td><strong><?php echo $kamar['nomor_kamar']; ?></strong></td>
                            <td><?php echo $kamar['nama_tipe']; ?></td>
                            <td>Lantai <?php echo $kamar['lantai']; ?></td>
                            <td><?php echo format_rupiah($kamar['harga_per_malam']); ?></td>
                            <td><?php echo $kamar['kapasitas']; ?> orang</td>
                            <td><?php echo status_badge($kamar['status']); ?></td>
                            <td>
                                <a href="?page=kamar&action=edit&id=<?php echo $kamar['id_kamar']; ?>" 
                                   class="btn btn-sm btn-warning btn-action">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="?page=kamar&action=delete&id=<?php echo $kamar['id_kamar']; ?>" 
                                   class="btn btn-sm btn-danger btn-action"
                                   onclick="return confirmDelete(<?php echo $kamar['id_kamar']; ?>, 'Kamar <?php echo $kamar['nomor_kamar']; ?>')">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">Tidak ada data kamar</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- pagination -->
        <?php if ($total_hal > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $no_hal <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=kamar&no_hal=<?php echo $no_hal - 1; ?>&search=<?php echo $search; ?>&filter_status=<?php echo $filter_status; ?>">
                        Previous
                    </a>
                </li>
                
                <?php for ($i = 1; $i <= $total_hal; $i++): ?>
                <li class="page-item <?php echo $no_hal == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=kamar&no_hal=<?php echo $i; ?>&search=<?php echo $search; ?>&filter_status=<?php echo $filter_status; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $no_hal >= $total_hal ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=kamar&no_hal=<?php echo $no_hal + 1; ?>&search=<?php echo $search; ?>&filter_status=<?php echo $filter_status; ?>">
                        Next
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>