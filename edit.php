<?php
// Koneksi ke database
$koneksi = mysqli_connect("localhost", "root", "", "ukk2025_todolist");

// Periksa koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil data task berdasarkan ID
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $query = "SELECT * FROM task WHERE id = '$id'";
    $result = mysqli_query($koneksi, $query);
    $task = mysqli_fetch_assoc($result);

    if (!$task) {
        echo "<script>alert('Task tidak ditemukan!'); window.location.href='index.php';</script>";
        exit;
    }
}

// Proses update task
if (isset($_POST['update_task'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $task_name = mysqli_real_escape_string($koneksi, $_POST['task']);
    $description = mysqli_real_escape_string($koneksi, $_POST['description']);
    $priority = mysqli_real_escape_string($koneksi, $_POST['priority']);
    $due_date = mysqli_real_escape_string($koneksi, $_POST['due_date']);
    // $status = mysqli_real_escape_string($koneksi, $_POST['status']);

    $query = "UPDATE task SET task='$task_name', description='$description', priority='$priority', due_date='$due_date' WHERE id='$id'";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Task berhasil diperbarui'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan: " . mysqli_error($koneksi) . "');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task | UKK RPL 2025</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <div class="container mt-4">
        <h2 class="text-center mb-3">
            <i class="fas fa-edit "></i> Edit Task
        </h2>

        <form action="" method="post" class="border rounded bg-light p-4 shadow-sm">

            <input type="hidden" name="id" value="<?= $task['id'] ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold">📝 Nama Task</label>
                <input type="text" name="task" class="form-control" value="<?= $task['task'] ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">🗒️ Deskripsi</label>
                <textarea name="description" class="form-control" rows="3" required><?= $task['description'] ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">⚡ Prioritas</label>
                <select name="priority" class="form-select" required>
                    <option value="1" <?= $task['priority'] == 1 ? "selected" : "" ?>>🔵 Low</option>
                    <option value="2" <?= $task['priority'] == 2 ? "selected" : "" ?>>🟡 Medium</option>
                    <option value="3" <?= $task['priority'] == 3 ? "selected" : "" ?>>🔴 High</option>
                </select>
            </div>

            <div class="mb-3">
    <label class="form-label fw-semibold">📅 Tanggal Deadline</label>
    <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime($task['due_date'])) ?>" required>
</div>


            <button class="btn btn-warning w-100 mt-3 fw-semibold" name="update_task">🔄 Update Task</button>
        </form>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let dateInput = document.querySelector('input[name="due_date"]');
            let today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);
        });
    </script>

</body>

</html>