<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit; }

require_once __DIR__ . '/student.php';

$studentObj = new Student();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    if ($deleteId > 0) {
        $studentObj->delete($deleteId);
        header("Location: home.php?msg=deleted");
        exit;
    }
}

$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$students = $studentObj->getAll();

if (!empty($search)) {
    $students = array_filter($students, function($s) use ($search) {
        $fullName = strtolower($s['first_name'] . ' ' . $s['last_name']);
        return str_contains($fullName, strtolower($search))
            || str_contains(strtolower($s['id_number']), strtolower($search))
            || str_contains(strtolower($s['course']),    strtolower($search));
    });
}

$totalStudents = $studentObj->countAll();

$msg = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $msg = 'Student added successfully!';
    if ($_GET['msg'] === 'updated') $msg = 'Student updated successfully!';
    if ($_GET['msg'] === 'deleted') $msg = 'Student deleted.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Records</title>
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; color: #222; min-height: 100vh; }

    .topbar {
        background: #d68bec; color: #000000; padding: 14px 32px;
        display: flex; align-items: center; gap: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,.3);
    }
    .topbar h1 { font-size: 1.4rem; letter-spacing: 1px; }
    .topbar .badge {
        background: #e94560; color: #fff; border-radius: 12px;
        padding: 2px 10px; font-size: .78rem; font-weight: 600;
    }
    .topbar .user-info {
        margin-left: auto; display: flex; align-items: center; gap: 14px; font-size: .88rem;
    }
    .topbar .user-info span { color: #070707; }
    .topbar .user-info strong { color: #050505; }
    .btn-logout {
        background: #e94560; color: #fff; border: none; border-radius: 8px;
        padding: 7px 14px; font-size: .85rem; font-weight: 600;
        cursor: pointer; text-decoration: none; transition: background .2s;
    }
    .btn-logout:hover { background: #c73350; }

    .container { max-width: 860px; margin: 36px auto; padding: 0 16px; }

    .controls {
        display: flex; justify-content: space-between;
        align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 22px;
    }
    .search-form { display: flex; gap: 8px; flex: 1; }
    .search-form input {
        flex: 1; padding: 9px 14px; border: 1.5px solid #a3c5ec;
        border-radius: 8px; font-size: .95rem; outline: none;
    }
    .search-form input:focus { border-color: #e94560; }

    .btn {
        padding: 9px 18px; border: none; border-radius: 8px; cursor: pointer;
        font-size: .93rem; font-weight: 600; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px; transition: background .2s;
    }
    .btn-search { background: #e79ca8; color: #030303; }
    .btn-search:hover { background: #c73350; }
    .btn-add    { background: #e79ca8; color: #030303;}
    .btn-add:hover    { background: #c73350; }
    .btn-sm     { padding: 5px 12px; font-size: .82rem; }
    .btn-edit   { background: #2ebb12; color: #fff; }
    .btn-edit:hover   { background: #005f90; }
    .btn-delete { background: #d62828; color: #fff; }
    .btn-delete:hover { background: #b01c1c; }
    .btn-reset  { background: #adb5bd; color: #222; }

    .flash { padding: 12px 18px; border-radius: 8px; margin-bottom: 18px; font-weight: 600; }
    .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .flash-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }

    .stats {
        background: #ebda92; border-radius: 10px; padding: 14px 20px;
        margin-bottom: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.08);
        font-size: .91rem; color: #030303;
    }
    .stats strong { color: #1a1a2e; font-size: 1.1rem; }

    .card-list { display: flex; flex-direction: column; gap: 14px; }

    .student-card {
        background: #a3c4eb; border-radius: 12px; padding: 18px 22px;
        box-shadow: 0 2px 8px rgba(0,0,0,.07);
        display: flex; justify-content: space-between; align-items: flex-start;
        gap: 16px; border-left: 4px solid #e94560; transition: box-shadow .2s;
    }
    .student-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.12); }

    .student-info h3 { font-size: 1.05rem; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
    .student-info p  { font-size: .86rem; color: #666; line-height: 1.6; }
    .course-badge {
        display: inline-block; background: #e8f4fd; color: #0077b6;
        border-radius: 6px; padding: 2px 8px; font-size: .78rem; font-weight: 700; margin-top: 4px;
    }
    .card-actions { display: flex; gap: 8px; flex-shrink: 0; }

    .empty { text-align: center; padding: 60px 20px; background: #fff; border-radius: 12px; color: #aaa; }
    .empty .icon { font-size: 3rem; margin-bottom: 10px; }
</style>
</head>
<body>

<div class="topbar">
    <h1>Student Records</h1>
    <span class="badge"><?= $totalStudents ?> Students</span>
    <div class="user-info">
        <span>Logged in as <strong><?= htmlspecialchars($_SESSION['user']) ?></strong></span>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</div>

<div class="container">

    <?php if (!empty($msg)): ?>
        <div class="flash <?= ($msg === 'Student deleted.') ? 'flash-warning' : 'flash-success' ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div class="controls">
        <form class="search-form" method="GET" action="home.php">
            <input type="text" name="search"
                   placeholder="Search by name, ID, or course…"
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-search">Search</button>
            <?php if (!empty($search)): ?>
                <a href="home.php" class="btn btn-reset">Clear</a>
            <?php endif; ?>
        </form>
        <a href="create_student.php" class="btn btn-add">Add Student</a>
    </div>

    <div class="stats">
        Showing <strong><?= count($students) ?></strong> of
        <strong><?= $totalStudents ?></strong> student<?= $totalStudents !== 1 ? 's' : '' ?>
        <?php if (!empty($search)): ?>
            &mdash; filtered by "<em><?= htmlspecialchars($search) ?></em>"
        <?php endif; ?>
    </div>

    <div class="card-list">
        <?php if (empty($students)): ?>
            <div class="empty">
                <div class="icon">&#128100;</div>
                <p>No students found<?= !empty($search) ? ' matching your search' : '' ?>.</p>
            </div>
        <?php else: ?>
            <?php foreach ($students as $student): ?>
                <div class="student-card">
                    <div class="student-info">
                        <h3><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h3>
                        <p>
                            <?= htmlspecialchars($student['email']) ?><br>
                            ID: <?= htmlspecialchars($student['id_number']) ?>
                        </p>
                        <span class="course-badge"><?= htmlspecialchars($student['course']) ?></span>
                    </div>
                    <div class="card-actions">
                        <a href="edit_student.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
                        <a href="home.php?delete=<?= $student['id'] ?>"
                           class="btn btn-sm btn-delete"
                           onclick="return confirm('Delete <?= htmlspecialchars($student['first_name']) ?>?')">Delete
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
</body>
</html>