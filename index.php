<?php
// Koneksi ke database
$koneksi = mysqli_connect("localhost", "root", "", "ukk2025_todolist");

// Periksa koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Tambah task
if (isset($_POST['add_task'])) {
    $task = mysqli_real_escape_string($koneksi, $_POST['task']);
    $priority = mysqli_real_escape_string($koneksi, $_POST['priority']);
    $description = mysqli_real_escape_string($koneksi, $_POST['description']);
    $due_date = mysqli_real_escape_string($koneksi, $_POST['due_date']);

    if (!empty($task) && !empty($priority) && !empty($due_date) && !empty($description)) {
        $query = "INSERT INTO task (task, priority, description, due_date, status) VALUES ('$task', '$priority', '$description', '$due_date', '0')";
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Task berhasil ditambahkan'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan: " . mysqli_error($koneksi) . "');</script>";
        }
    } else {
        echo "<script>alert('Semua kolom harus diisi');</script>";
    }
}

// Menandai task sebagai selesai
if (isset($_GET['complete'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['complete']);
    mysqli_query($koneksi, "UPDATE task SET status = '1' WHERE id = '$id'");
    echo "<script>alert('Task berhasil diselesaikan'); window.location.href='index.php';</script>";
}

// Hapus task
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['delete']);
    mysqli_query($koneksi, "DELETE FROM task WHERE id = '$id'");
    echo "<script>alert('Task berhasil dihapus'); window.location.href='index.php';</script>";
}
if (isset($_GET['complete'])) {
    $task_id = intval($_GET['complete']);
    mysqli_query($koneksi, "UPDATE task SET status = 1 WHERE id = $task_id");
    header("Location: index.php");
    exit();
}

// Handle perubahan status menjadi Belum Selesai
if (isset($_GET['uncomplete'])) {
    $task_id = intval($_GET['uncomplete']);
    mysqli_query($koneksi, "UPDATE task SET status = 0 WHERE id = $task_id");
    header("Location: index.php");
    exit();
}
// Pagination setup
$limit = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi To-Do List | UKK RPL 2025</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <div class="container mt-4">
        <h2 class="text-center mb-3">
            <i class="fas fa-list-check me-2"></i> To-Do List
        </h2>

        <!-- Form Tambah Task -->
        <form action="" method="post" class="border rounded bg-light p-4 shadow-sm">
            <div class=" mb-4">
                <h5 class="d-inline-flex align-items-center bg-primary text-white fw-semibold p-2 rounded-3">
                    Tambah Task Baru
                </h5>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">📝 Nama Task</label>
                <input type="text" name="task" class="form-control" placeholder="Masukan Nama Task" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">🗒️ Deskripsi</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Deskripsikan task yang ingin kamu selesaikan" required></textarea>
            </div>

            <div class="mb-3 d-flex flex-row justify-content-between">
                <div class="flex-fill">
                    <label class="form-label fw-semibold">⚡ Prioritas</label>
                    <select name="priority" class="form-select" required>
                        <option value="">-- Pilih Prioritas --</option>
                        <option value="1">🔵 Low</option>
                        <option value="2">🟡 Medium</option>
                        <option value="3">🔴 High</option>
                    </select>
                </div>

                <div class="flex-fill ms-3">
                    <label class="form-label fw-semibold">📅 Tanggal Deadline</label>
                    <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <button class="button btn-primary w-100 mt-3 fw-semibold" name="add_task">➕ Tambah Task</button>
        </form>

        <hr>
        <!-- Form Pencarian dan Filter -->
        <form method="GET" class="mb-4">
            <div class="row g-2 align-items-center">
                <!-- Kolom Pencarian -->
                <div class="col-md-6">
                    <div class="input-group shadow-sm">
                        <input
                            type="text"
                            name="search"
                            class="form-control rounded-start"
                            placeholder="🔍 Cari task..."
                            value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
                        <button class="btn btn-outline-primary" type="submit">Cari</button>
                    </div>
                </div>

                <!-- Kolom Filter Prioritas -->
                <div class="col-md-6">
                    <div class="input-group shadow-sm">
                        <select name="filter_priority" class="form-select">
                            <option value="">🎯 Filter Prioritas</option>
                            <option value="1" <?= isset($_GET['filter_priority']) && $_GET['filter_priority'] == "1" ? "selected" : "" ?>>Low</option>
                            <option value="2" <?= isset($_GET['filter_priority']) && $_GET['filter_priority'] == "2" ? "selected" : "" ?>>Medium</option>
                            <option value="3" <?= isset($_GET['filter_priority']) && $_GET['filter_priority'] == "3" ? "selected" : "" ?>>High</option>
                        </select>
                        <button class="btn btn-outline-secondary" type="submit">Terapkan</button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Tabel Task -->
        <table class="table table-hover align-middle shadow-sm rounded bg-white">
            <thead class="table-light">
                <tr class="text-center">
                    <th>No</th>
                    <th>Task</th>
                    <th>Deskripsi</th>
                    <th>Prioritas</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
                $filter_priority = isset($_GET['filter_priority']) ? mysqli_real_escape_string($koneksi, $_GET['filter_priority']) : '';

                $total_query = "SELECT COUNT(*) AS total FROM task WHERE 1";
                if (!empty($search)) $total_query .= " AND task LIKE '%$search%'";
                if (!empty($filter_priority)) $total_query .= " AND priority = '$filter_priority'";
                $total_result = mysqli_query($koneksi, $total_query);
                $total_row = mysqli_fetch_assoc($total_result);
                $total_data = $total_row['total'];
                $total_pages = ceil($total_data / $limit);

                $query = "SELECT * FROM task WHERE 1";
                if (!empty($search)) $query .= " AND task LIKE '%$search%'";
                if (!empty($filter_priority)) $query .= " AND priority = '$filter_priority'";
                $query .= " ORDER BY status ASC, priority DESC, due_date ASC LIMIT $start, $limit";
                $result = mysqli_query($koneksi, $query);

                if (mysqli_num_rows($result) > 0) {
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                        if ($row['priority'] == 1) {
                            $prioritas = "<span class='badge bg-info text-dark'>Low</span>";
                        } elseif ($row['priority'] == 2) {
                            $prioritas = "<span class='badge bg-warning text-dark'>Medium</span>";
                        } elseif ($row['priority'] == 3) {
                            $prioritas = "<span class='badge bg-danger'>High</span>";
                        } else {
                            $prioritas = "-";
                        }


                        $status = $row['status'] == 0
                            ? "<span class='badge bg-secondary'>Belum Selesai</span>"
                            : "<span class='badge bg-success'>Selesai</span>";

                        echo "<tr>
                    <td class='text-center'>{$no}</td>
                    <td>{$row['task']}</td>
                    <td class='desc' style='max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'>{$row['description']}</td>

                    <td class='text-center'>{$prioritas}</td>
                    <td class='text-center'>📅 " . date('Y-m-d', strtotime($row['due_date'])) . "</td>
                    <td class='text-center'>{$status}</td>
                    <td class='text-center'>
                        <div class='d-flex flex-wrap gap-1 justify-content-center'>
                            " . ($row['status'] == 0
                            ? "<a href='?complete={$row['id']}' class='btn btn-sm btn-outline-success' title='Tandai Selesai'><i class='fas fa-check'></i></a>"
                            : "<a href='?uncomplete={$row['id']}' class='btn btn-sm btn-outline-secondary' title='Batalkan Selesai'><i class='fas fa-undo'></i></a>") . "
                            <a href='#' class='btn btn-sm btn-outline-primary' title='Lihat Detail' data-bs-toggle='modal' data-bs-target='#detailModal{$row['id']}'><i class='fas fa-eye'></i></a>
                            <a href='edit.php?id={$row['id']}' class='btn btn-sm btn-outline-warning' title='Edit'><i class='fas fa-edit'></i></a>
                            <a href='?delete={$row['id']}' class='btn btn-sm btn-outline-danger' title='Hapus' onclick='return confirm(\"Yakin ingin menghapus task ini?\")'><i class='fas fa-trash'></i></a>
                        </div>
                    </td>
                </tr>";
                        echo "
                        <!-- Modal Detail -->
                        <div class='modal fade' id='detailModal{$row['id']}' tabindex='-1' aria-labelledby='detailModalLabel{$row['id']}' aria-hidden='true'>
                            <div class='modal-dialog modal-dialog-centered'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='detailModalLabel{$row['id']}'>Detail Task</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <div class='modal-body text-start'>
                                        <p><strong>Task:</strong> {$row['task']}</p>
                                        <p><strong>Deskripsi:</strong><br>{$row['description']}</p>
                                        <p><strong>Prioritas:</strong> {$prioritas}</p>
                                        <p><strong>Deadline:</strong> 📅 " . date('Y-m-d', strtotime($row['due_date'])) . "</p>
                                        <p><strong>Status:</strong> {$status}</p>
                                    </div>
                                    <div class='modal-footer'>
                                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>";
                        $no++;
                    }
                } else {

                    echo "<tr><td colspan='7' class='text-center text-muted'>🔍 Tidak ada task ditemukan</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>&filter_priority=<?= $filter_priority ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

    </div>



    <!-- JavaScript untuk validasi tanggal -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let dateInput = document.querySelector('input[name="due_date"]');
            let today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>