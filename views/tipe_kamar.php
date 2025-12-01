<?php
$message = '';
$message_type = '';

// CREATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    try {
        $nama_tipe = sanitize_input($_POST['nama_tipe']);
        $harga = sanitize_input($_POST['harga_per_malam']);
        $kapasitas = sanitize_input($_POST['kapasitas']);
        $deskripsi = sanitize_input($_POST['deskripsi']);
        $foto_url = sanitize_input($_POST['foto_url']);
        
        $stmt = $conn->prepare("
            INSERT INTO tipe_kamar (nama_tipe, harga_per_malam, kapasitas, deskripsi, foto_url) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nama_tipe, $harga, $kapasitas, $deskripsi, $foto_url]);
        
        $message = "Tipe kamar berhasil ditambahkan!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    try {
        $id = sanitize_input($_POST['id_tipe']);
        $nama_tipe = sanitize_input($_POST['nama_tipe']);
        $harga = sanitize_input($_POST['harga_per_malam']);
        $kapasitas = sanitize_input($_POST['kapasitas']);
        $deskripsi = sanitize_input($_POST['deskripsi']);
        $foto_url = sanitize_input($_POST['foto_url']);
        
        $stmt = $conn->prepare("
            UPDATE tipe_kamar 
            SET nama_tipe = ?, harga_per_malam = ?, kapasitas = ?, deskripsi = ?, foto_url = ? 
            WHERE id_tipe = ?
        ");
        $stmt->execute([$nama_tipe, $harga, $kapasitas, $deskripsi, $foto_url, $id]);
        
        $message = "Tipe kamar berhasil diupdate!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// DELETE
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM tipe_kamar WHERE id_tipe = ?");
        $stmt->execute([sanitize_input($_GET['id'])]);
        $message = "Tipe kamar berhasil dihapus!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// READ
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$where = $search ? "WHERE nama_tipe ILIKE ?" : "";
$params = $search ? ["%$search%"] : [];

try {
    $stmt = $conn->prepare("SELECT * FROM tipe_kamar $where ORDER BY harga_per_malam");
    $stmt->execute($params);
    $tipe_list = $stmt->fetchAll();
    
    // Get fasilitas
    $fasilitas_stmt = $conn->query("SELECT * FROM fasilitas ORDER BY kategori, nama_fasilitas");
    $fasilitas_list = $fasilitas_stmt->fetchAll();
} catch (PDOException $e) {
    $error = $e->getMessage();
}

$edit_data = null;
$edit_fasilitas = [];
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM tipe_kamar WHERE id_tipe = ?");
    $stmt->execute([sanitize_input($_GET['id'])]);
    $edit_data = $stmt->fetch();
    
    // Get fasilitas for this tipe
    $fas_stmt = $conn->prepare("SELECT id_fasilitas FROM tipe_fasilitas WHERE id_tipe = ?");
    $fas_stmt->execute([$edit_data['id_tipe']]);
    $edit_fasilitas = $fas_stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<h1 class="page-title">Tipe Kamar</h1>

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
            <i class="bi bi-<?php echo $edit_data ? 'pencil' : 'plus-circle'; ?> me-2"></i>
            <?php echo $edit_data ? 'Edit Tipe Kamar' : 'Tambah Tipe Kamar'; ?>
        </h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Tipe <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_tipe" 
                            value="<?php echo $edit_data ? $edit_data['nama_tipe'] : ''; ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Harga per Malam <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="harga_per_malam" 
                            value="<?php echo $edit_data ? $edit_data['harga_per_malam'] : ''; ?>" 
                            min="0" step="1000" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Kapasitas <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="kapasitas" 
                            value="<?php echo $edit_data ? $edit_data['kapasitas'] : '2'; ?>" 
                            min="1" max="10" required>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea class="form-control" name="deskripsi" rows="3"><?php echo $edit_data ? $edit_data['deskripsi'] : ''; ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">URL Foto</label>
                <input type="text" class="form-control" name="foto_url" 
                    value="<?php echo $edit_data ? $edit_data['foto_url'] : ''; ?>" 
                    placeholder="/img/nama-foto.jpg">
            </div>
            
            <?php if ($edit_data): ?>
                <input type="hidden" name="id_tipe" value="<?php echo $edit_data['id_tipe']; ?>">
                <button type="submit" name="update" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Update
                </button>
                <a href="?page=tipe_kamar" class="btn btn-secondary">
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
            <input type="hidden" name="page" value="tipe_kamar">
            <div class="col-md-10">
                <input type="text" class="form-control search-box" name="search" 
                    placeholder="🔍 Cari tipe kamar..." 
                    value="<?php echo $search; ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Cari</button>
            </div>
        </form>
    </div>
</div>

<!-- Card Grid -->
<div class="row g-4">
    <?php foreach ($tipe_list as $tipe): ?>
    <div class="col-md-4">
        <div class="card h-100 table-card">
            <div class="card-body">
                <h5 class="card-title"><?php echo $tipe['nama_tipe']; ?></h5>
                <h4 class="text-primary mb-3"><?php echo format_rupiah($tipe['harga_per_malam']); ?>/malam</h4>
                
                <p class="card-text text-muted mb-3">
                    <?php echo $tipe['deskripsi'] ?: 'Tidak ada deskripsi'; ?>
                </p>
                
                <div class="mb-3">
                    <span class="badge bg-info me-2">
                        <i class="bi bi-people"></i> <?php echo $tipe['kapasitas']; ?> orang
                    </span>
                </div>
                
                <?php
                // Get fasilitas untuk tipe ini
                $fas_stmt = $conn->prepare("
                    SELECT f.nama_fasilitas, f.kategori 
                    FROM fasilitas f
                    JOIN tipe_fasilitas tf ON f.id_fasilitas = tf.id_fasilitas
                    WHERE tf.id_tipe = ?
                    ORDER BY f.kategori, f.nama_fasilitas
                    LIMIT 5
                ");
                $fas_stmt->execute([$tipe['id_tipe']]);
                $fasilitas_tipe = $fas_stmt->fetchAll();
                ?>
                
                <?php if (count($fasilitas_tipe) > 0): ?>
                <div class="mb-3">
                    <small class="text-muted d-block mb-1"><strong>Fasilitas:</strong></small>
                    <?php foreach ($fasilitas_tipe as $fas): ?>
                        <small class="badge bg-light text-dark me-1 mb-1"><?php echo $fas['nama_fasilitas']; ?></small>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="d-flex gap-2">
                    <a href="?page=tipe_kamar&action=edit&id=<?php echo $tipe['id_tipe']; ?>" 
                       class="btn btn-warning btn-sm flex-fill">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="?page=tipe_kamar&action=delete&id=<?php echo $tipe['id_tipe']; ?>" 
                       class="btn btn-danger btn-sm flex-fill"
                       onclick="return confirmDelete(<?php echo $tipe['id_tipe']; ?>, '<?php echo $tipe['nama_tipe']; ?>')">
                        <i class="bi bi-trash"></i> Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (count($tipe_list) == 0): ?>
<div class="card table-card">
    <div class="card-body text-center py-5">
        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
        <p class="text-muted mt-3">Tidak ada data tipe kamar</p>
    </div>
</div>
<?php endif; ?>