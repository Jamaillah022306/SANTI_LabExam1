<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit; }

require_once __DIR__ . '/student.php';

$studentObj = new Student();
$errors     = [];
$formData   = ['id_number' => '', 'first_name' => '', 'last_name' => '', 'email' => '', 'course' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'id_number'  => $_POST['id_number']  ?? '',
        'first_name' => $_POST['first_name'] ?? '',
        'last_name'  => $_POST['last_name']  ?? '',
        'email'      => $_POST['email']      ?? '',
        'course'     => $_POST['course']     ?? '',
    ];

    $errors = $studentObj->validate($formData);

    if (empty($errors)) {
        if ($studentObj->create($formData)) {
            header("Location: home.php?msg=created");
            exit;
        } else {
            $errors[] = "An error occurred. The ID or Email may already exist.";
        }
    }
}

$courses = ['BSCS', 'BSCS-SA', 'BSIT', 'BSIS', 'BSECE', 'BSCE', 'BSME', 'BSN', 'BSBA', 'BSA'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Student Record</title>
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; min-height: 100vh; }

    .topbar {
        background: #1a1a2e; color: #fff; padding: 14px 32px;
        display: flex; align-items: center; gap: 14px; box-shadow: 0 2px 8px rgba(0,0,0,.3);
    }
    .topbar h1 { font-size: 1.4rem; letter-spacing: 1px; }
    .topbar a  { color: #e94560; text-decoration: none; font-size: .9rem; margin-left: auto; }
    .topbar a:hover { text-decoration: underline; }

    .container { max-width: 540px; margin: 40px auto; padding: 0 16px; }

    .card {
        background: #fff; border-radius: 14px; padding: 36px 38px;
        box-shadow: 0 4px 20px rgba(0,0,0,.10);
    }
    .card h2 {
        font-size: 1.35rem; color: #1a1a2e; margin-bottom: 24px;
        padding-bottom: 14px; border-bottom: 2px solid #f0f2f5;
    }

    .error-list {
        background: #fff5f5; border: 1px solid #f5c6cb;
        border-radius: 8px; padding: 12px 18px; margin-bottom: 20px;
    }
    .error-list p  { color: #721c24; font-size: .88rem; font-weight: 600; margin-bottom: 4px; }
    .error-list ul { padding-left: 16px; color: #721c24; font-size: .85rem; }
    .error-list li { margin-bottom: 3px; }

    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: .88rem; font-weight: 600; color: #444; margin-bottom: 6px; }
    .form-group label span { color: #e94560; }
    .form-row { display: flex; gap: 14px; }
    .form-row .form-group { flex: 1; }

    input[type="text"], input[type="email"], select {
        width: 100%; padding: 10px 14px; border: 1.5px solid #c7cdd4;
        border-radius: 8px; font-size: .95rem; color: #222; outline: none;
        background: #fafafa; transition: border .2s, box-shadow .2s;
    }
    input:focus, select:focus {
        border-color: #e94560; box-shadow: 0 0 0 3px rgba(233,69,96,.12); background: #fff;
    }

    .form-actions { display: flex; gap: 12px; margin-top: 28px; }
    .btn {
        flex: 1; padding: 11px; border: none; border-radius: 8px; cursor: pointer;
        font-size: .95rem; font-weight: 700; text-align: center;
        text-decoration: none; display: inline-block; transition: background .2s;
    }
    .btn-submit { background: #e94560; color: #fff; }
    .btn-submit:hover { background: #c73350; }
    .btn-cancel { background: #f0f2f5; color: #555; }
    .btn-cancel:hover { background: #dee2e6; }

    footer { text-align: center; padding: 24px; color: #aaa; font-size: .8rem; margin-top: 30px; }
</style>
</head>
<body>

<div class="topbar">
    <h1>&#127979; Student Records</h1>
    <a href="home.php">&#8592; Back to List</a>
</div>

<div class="container">
    <div class="card">
        <h2>Create Student Record</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <p>Please fix the following:</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="create_student.php">

            <div class="form-group">
                <label>ID Number <span>*</span></label>
                <input type="text" name="id_number" placeholder="e.g. 517307"
                       value="<?= htmlspecialchars($formData['id_number']) ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>First Name <span>*</span></label>
                    <input type="text" name="first_name" placeholder="Juan"
                           value="<?= htmlspecialchars($formData['first_name']) ?>">
                </div>
                <div class="form-group">
                    <label>Last Name <span>*</span></label>
                    <input type="text" name="last_name" placeholder="Dela Cruz"
                           value="<?= htmlspecialchars($formData['last_name']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Email <span>*</span></label>
                <input type="email" name="email" placeholder="juan@umindanao.edu.ph"
                       value="<?= htmlspecialchars($formData['email']) ?>">
            </div>

            <div class="form-group">
                <label>Course <span>*</span></label>
                <select name="course">
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c ?>" <?= ($formData['course'] === $c) ? 'selected' : '' ?>>
                            <?= $c ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-submit">Add Student</button>
                <a href="home.php" class="btn btn-cancel">Cancel</a>
            </div>

        </form>
    </div>
</div>
</body>
</html>