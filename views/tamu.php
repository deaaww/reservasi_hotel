<?php
$message = '';
$message_type = '';

// CREATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    try {
        $nama = sanitize_input($_POST['nama_tamu']);
        $telp = sanitize_input($_POST['no_telp']);
        $email = sanitize_input($_POST['email']);
        $password = sanitize_input($_POST['password']);
        
        $stmt = $conn->prepare("
            INSERT INTO tamu (nama_tamu, no_telp, email, password) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$nama, $telp, $email, $password]);
        
        $message = "Tamu berhasil ditambahkan!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    try {
        $id = sanitize_input($_POST['id_tamu']);
        $nama = sanitize_input($_POST['nama_tamu']);
        $telp = sanitize_input($_POST['no_telp']);
        $email = sanitize_input($_POST['email']);
        
        $stmt = $conn->prepare("
            UPDATE tamu 
            SET nama_tamu = ?, no_telp = ?, email = ? 
            WHERE id_tamu = ?
        ");
        $stmt->execute([$nama, $telp, $email, $id]);
        
        $message = "Data tamu berhasil diupdate!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// DELETE
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM tamu WHERE id_tamu = ?");
        $stmt->execute([sanitize_input($_GET['id'])]);
        $message = "Tamu berhasil dihapus!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// READ with pagination
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$records_per_page = 10;
$offset = ($page_num - 1) * $records_per_page;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

$where = $search ? "WHERE nama_tamu ILIKE ? OR no_telp ILIKE ? OR email ILIKE ?" : "";
$params = $search ? ["%$search%", "%$search%", "%$search%"] : [];

try {
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM tamu $where");
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $records_per_page);
    
    $params[] = $records_per_page;
    $params[] = $offset;
    $stmt = $conn->prepare("SELECT * FROM tamu $where ORDER BY id_tamu ASC LIMIT ? OFFSET ?");
    $stmt->execute($params);
    $tamu_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = $e->getMessage();
}

$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM tamu WHERE id_tamu = ?");
    $stmt->execute([sanitize_input($_GET['id'])]);
    $edit_data = $stmt->fetch();
}
?>

<h1 class="page-title">Data Tamu</h1>

<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Form -->
<div class="card table-card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-<?php echo $edit_data ? 'pencil' : 'person-plus'; ?> me-2"></i>
            <?php echo $edit_data ? 'Edit Tamu' : 'Tambah Tamu Baru'; ?>
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" onsubmit="return validateTamuForm()">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_tamu" id="nama_tamu"
                            value="<?php echo $edit_data ? $edit_data['nama_tamu'] : ''; ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="no_telp" id="no_telp"
                            value="<?php echo $edit_data ? $edit_data['no_telp'] : ''; ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email"
                            value="<?php echo $edit_data ? $edit_data['email'] : ''; ?>">
                    </div>
                </div>
                <?php if (!$edit_data): ?>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($edit_data): ?>
                <input type="hidden" name="id_tamu" value="<?php echo $edit_data['id_tamu']; ?>">
                <button type="submit" name="update" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Update
                </button>
                <a href="?page=tamu" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-2"></i>Batal
                </a>
            <?php else: ?>
                <button type="submit" name="tambah" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Simpan
                </button>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Search -->
<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="tamu">
            <div class="col-md-10">
                <input type="text" class="form-control search-box" name="search" 
                    placeholder="🔍 Cari nama, telepon, atau email..." 
                    value="<?php echo $search; ?>"
                    onkeyup="this.form.submit()">
            </div>
            <div class="col-md-2">
                <a href="?page=tamu" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card table-card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Daftar Tamu</h5>
        <span class="badge bg-primary"><?php echo $total_records; ?> Tamu</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>No. Telepon</th>
                        <th>Email</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tamu_list as $tamu): ?>
                    <tr>
                        <td><?php echo $tamu['id_tamu']; ?></td>
                        <td><strong><?php echo htmlspecialchars($tamu['nama_tamu']); ?></strong></td>
                        <td><?php echo $tamu['no_telp']; ?></td>
                        <td><?php echo $tamu['email']; ?></td>
                        <td>
                            <a href="?page=tamu&action=edit&id=<?php echo $tamu['id_tamu']; ?>" 
                               class="btn btn-sm btn-warning btn-action">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="?page=tamu&action=delete&id=<?php echo $tamu['id_tamu']; ?>" 
                               class="btn btn-sm btn-danger btn-action"
                               onclick="return confirmDelete(<?php echo $tamu['id_tamu']; ?>, '<?php echo $tamu['nama_tamu']; ?>')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page_num == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=tamu&page_num=<?php echo $i; ?>&search=<?php echo $search; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<script>
function validateTamuForm() {
    const nama = document.getElementById('nama_tamu').value;
    const telp = document.getElementById('no_telp').value;
    
    if (nama.trim().length < 3) {
        alert('Nama minimal 3 karakter!');
        return false;
    }
    
    if (telp.length < 10) {
        alert('Nomor telepon tidak valid!');
        return false;
    }
    
    return true;
}
</script>