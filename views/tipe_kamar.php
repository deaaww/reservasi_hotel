<?php
$message = '';
$message_type = '';

/* upload foto */
function upload_foto($file_input_name, $old_file = null) {
    $target_dir = "uploads/tipe_kamar/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] == 4) {
        return $old_file; // tidak upload baru
    }

    $file = $_FILES[$file_input_name];
    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        return null;
    }

    $new_name = "tipe_" . time() . "_" . rand(100, 999) . "." . $ext;
    $target_file = $target_dir . $new_name;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        if ($old_file && file_exists($target_dir . $old_file)) {
            @unlink($target_dir . $old_file);
        }
        return $new_name;
    }

    return null;
}

/* create */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    try {
        $nama = sanitize_input($_POST['nama_tipe']);
        $harga = sanitize_input($_POST['harga_per_malam']);
        $kapasitas = sanitize_input($_POST['kapasitas']);
        $deskripsi = sanitize_input($_POST['deskripsi']);

        // upload foto
        $foto = upload_foto('foto_file');

        if ($foto === null) {
            $message = "Gagal mengupload foto! Pastikan format JPG/PNG/WEBP.";
            $message_type = "danger";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO tipe_kamar (nama_tipe, harga_per_malam, kapasitas, deskripsi, foto_url)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nama, $harga, $kapasitas, $deskripsi, $foto]);

            $new_id = $conn->lastInsertId();

            // Insert fasilitas
            if (!empty($_POST['fasilitas'])) {
                foreach ($_POST['fasilitas'] as $f) {
                    $conn->prepare("INSERT INTO tipe_fasilitas (id_tipe, id_fasilitas) VALUES (?, ?)")
                         ->execute([$new_id, $f]);
                }
            }

            $message = "Tipe kamar berhasil ditambahkan!";
            $message_type = "success";
        }

    } catch (PDOException $e) {
        $message = $e->getMessage();
        $message_type = "danger";
    }
}

/* update */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    try {
        $id = $_POST['id_tipe'];
        $nama = sanitize_input($_POST['nama_tipe']);
        $harga = sanitize_input($_POST['harga_per_malam']);
        $kapasitas = sanitize_input($_POST['kapasitas']);
        $deskripsi = sanitize_input($_POST['deskripsi']);

        // get old photo
        $stmt = $conn->prepare("SELECT foto_url FROM tipe_kamar WHERE id_tipe = ?");
        $stmt->execute([$id]);
        $old_photo = $stmt->fetchColumn();

        // upload foto baru (jika ada)
        $foto = upload_foto('foto_file', $old_photo);

        if ($foto === null && isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] != 4) {
            $message = "Gagal upload foto!";
            $message_type = "danger";
        } else {
            $stmt = $conn->prepare("
                UPDATE tipe_kamar
                SET nama_tipe=?, harga_per_malam=?, kapasitas=?, deskripsi=?, foto_url=?
                WHERE id_tipe=?
            ");
            $stmt->execute([$nama, $harga, $kapasitas, $deskripsi, $foto, $id]);

            // update fasilitas
            $conn->prepare("DELETE FROM tipe_fasilitas WHERE id_tipe=?")->execute([$id]);

            if (!empty($_POST['fasilitas'])) {
                foreach ($_POST['fasilitas'] as $f) {
                    $conn->prepare("INSERT INTO tipe_fasilitas (id_tipe, id_fasilitas) VALUES (?, ?)")
                         ->execute([$id, $f]);
                }
            }

            $message = "Tipe kamar berhasil diperbarui!";
            $message_type = "success";
        }

    } catch (PDOException $e) {
        $message = $e->getMessage();
        $message_type = "danger";
    }
}

/* delete */
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];

    // delete foto
    $stmt = $conn->prepare("SELECT foto_url FROM tipe_kamar WHERE id_tipe=?");
    $stmt->execute([$id]);
    $foto = $stmt->fetchColumn();

    if ($foto && file_exists("uploads/tipe_kamar/" . $foto)) {
        @unlink("uploads/tipe_kamar/" . $foto);
    }

    $conn->prepare("DELETE FROM tipe_fasilitas WHERE id_tipe=?")->execute([$id]);
    $conn->prepare("DELETE FROM tipe_kamar WHERE id_tipe=?")->execute([$id]);

    $message = "Tipe kamar berhasil dihapus!";
    $message_type = "success";
}

/* read */
$edit = null;
$selected_fasilitas = [];

if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    $stmt = $conn->prepare("SELECT * FROM tipe_kamar WHERE id_tipe=?");
    $stmt->execute([$_GET['id']]);
    $edit = $stmt->fetch();

    $stmt = $conn->prepare("SELECT id_fasilitas FROM tipe_fasilitas WHERE id_tipe=?");
    $stmt->execute([$_GET['id']]);
    $selected_fasilitas = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$fasilitas = $conn->query("SELECT * FROM fasilitas ORDER BY kategori, nama_fasilitas")->fetchAll();
$tipe_list = $conn->query("SELECT * FROM tipe_kamar ORDER BY id_tipe DESC")->fetchAll();

?>

<h2 class="page-title">Tipe Kamar</h2>

<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
    <?= $message ?>
    <button class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- ========================= FORM TAMBAH / EDIT ========================= -->
<div class="card mb-4">
    <div class="card-header">
        <strong><?= $edit ? "Edit Tipe Kamar" : "Tambah Tipe Kamar" ?></strong>
    </div>

    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Tipe *</label>
                    <input type="text" required name="nama_tipe" class="form-control"
                        value="<?= $edit['nama_tipe'] ?? '' ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Harga per Malam *</label>
                    <input type="number" name="harga_per_malam" class="form-control"
                        value="<?= $edit['harga_per_malam'] ?? '' ?>" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Kapasitas *</label>
                    <input type="number" name="kapasitas" class="form-control"
                        value="<?= $edit['kapasitas'] ?? 2 ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="3"><?= $edit['deskripsi'] ?? '' ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Foto Kamar</label>
                <input type="file" name="foto_file" accept=".jpg,.png,.jpeg,.webp" class="form-control">

                <?php if ($edit && $edit['foto_url']): ?>
                    <img src="uploads/tipe_kamar/<?= $edit['foto_url'] ?>" 
                         class="mt-2 rounded" width="150">
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Fasilitas</label>

                <div class="row">
                    <?php foreach ($fasilitas as $f): ?>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input"
                                    name="fasilitas[]" value="<?= $f['id_fasilitas'] ?>"
                                    <?= in_array($f['id_fasilitas'], $selected_fasilitas) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $f['nama_fasilitas'] ?></label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($edit): ?>
                <input type="hidden" name="id_tipe" value="<?= $edit['id_tipe'] ?>">
                <button class="btn btn-primary" name="update">Update</button>
                <a href="?page=tipe_kamar" class="btn btn-secondary">Batal</a>
            <?php else: ?>
                <button class="btn btn-primary" name="tambah">Simpan</button>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- ========================= LIST CARD ========================= -->
<div class="row g-4">
<?php foreach ($tipe_list as $t): ?>
    <div class="col-md-4">
        <div class="card h-100">
            <?php if ($t['foto_url']): ?>
                <img src="uploads/tipe_kamar/<?= $t['foto_url'] ?>" class="card-img-top" height="160" style="object-fit: cover;">
            <?php endif; ?>

            <div class="card-body">
                <h5><?= $t['nama_tipe'] ?></h5>
                <p class="text-muted"><?= format_rupiah($t['harga_per_malam']) ?> / malam</p>
                <p><?= $t['deskripsi'] ?></p>

                <a href="?page=tipe_kamar&action=edit&id=<?= $t['id_tipe'] ?>" 
                   class="btn btn-warning btn-sm">Edit</a>

                <a onclick="return confirm('Hapus tipe kamar ini?')"
                   href="?page=tipe_kamar&action=delete&id=<?= $t['id_tipe'] ?>" 
                   class="btn btn-danger btn-sm">Hapus</a>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<?php if (count($tipe_list) == 0): ?>
<div class="alert alert-info text-center">Belum ada data tipe kamar</div>
<?php endif; ?>