<?php
include 'connect.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header("Location: index.php");
    exit;
}

// Handle update
if (isset($_POST['submit'])) {
    $first_name = $conn->real_escape_string(trim($_POST['firstname']));
    $last_name  = $conn->real_escape_string(trim($_POST['lastname']));
    $email      = $conn->real_escape_string(trim($_POST['email']));
    $gender     = $conn->real_escape_string($_POST['gender']);

    // Check duplicate email (excluding current user)
    $check = $conn->query("SELECT id FROM employees WHERE email='$email' AND id != $id");
    if ($check->num_rows > 0) {
        $error = "That email is already used by another employee.";
    } else {
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql = "UPDATE employees SET firstname='$first_name', lastname='$last_name',
                    email='$email', gender='$gender', password='$password'
                    WHERE id=$id";
        } else {
            $sql = "UPDATE employees SET firstname='$first_name', lastname='$last_name',
                    email='$email', gender='$gender'
                    WHERE id=$id";
        }

        if ($conn->query($sql)) {
            header("Location: index.php?success=updated");
            exit;
        } else {
            $error = "Update failed: " . $conn->error;
        }
    }
}

// Fetch current record
$res = $conn->query("SELECT * FROM employees WHERE id=$id");
if (!$res || $res->num_rows === 0) {
    header("Location: index.php");
    exit;
}
$emp = $res->fetch_assoc();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Employee</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg: #0f0f13;
      --surface: #18181f;
      --border: #2a2a38;
      --accent: #7c6af7;
      --accent2: #f7a26a;
      --text: #e8e8f0;
      --muted: #7070a0;
      --danger: #f76a6a;
      --radius: 14px;
    }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    body::before {
      content: '';
      position: fixed;
      bottom: -30%;
      right: -15%;
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(247,162,106,.12) 0%, transparent 65%);
      pointer-events: none;
    }

    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 2.5rem;
      width: 100%;
      max-width: 480px;
      position: relative;
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      color: var(--muted);
      text-decoration: none;
      font-size: .85rem;
      margin-bottom: 1.8rem;
      transition: color .2s;
    }
    .back-link:hover { color: var(--accent); }

    .top { display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; }
    .avatar-lg {
      width: 56px; height: 56px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 1.1rem; color: #fff; flex-shrink: 0;
    }
    .top h1 {
      font-family: 'DM Serif Display', serif;
      font-size: 1.7rem;
      line-height: 1.1;
    }
    .top p { color: var(--muted); font-size: .85rem; margin-top: .2rem; }

    .alert {
      padding: .75rem 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      font-size: .875rem;
    }
    .alert-error { background: rgba(247,106,106,.12); border: 1px solid rgba(247,106,106,.3); color: var(--danger); }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

    .field { margin-bottom: 1.2rem; }
    .field label {
      display: block;
      font-size: .8rem;
      font-weight: 600;
      letter-spacing: .05em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: .5rem;
    }
    .field input[type=text],
    .field input[type=email],
    .field input[type=password] {
      width: 100%;
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: 8px;
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      font-size: .95rem;
      padding: .7rem 1rem;
      transition: border-color .2s;
      outline: none;
    }
    .field input:focus { border-color: var(--accent); }
    .field small { display: block; margin-top: .35rem; color: var(--muted); font-size: .78rem; }

    .gender-group { display: flex; gap: 1.2rem; padding-top: .3rem; }
    .gender-opt { display: flex; align-items: center; gap: .5rem; cursor: pointer; }
    .gender-opt input { accent-color: var(--accent); width: 16px; height: 16px; cursor: pointer; }
    .gender-opt span { font-size: .95rem; }

    .btn-row { display: flex; gap: .8rem; margin-top: .5rem; }
    .btn-submit {
      flex: 1;
      padding: .85rem;
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-family: 'DM Sans', sans-serif;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: opacity .2s, transform .1s;
    }
    .btn-submit:hover { opacity: .85; }
    .btn-submit:active { transform: scale(.98); }
    .btn-back {
      padding: .85rem 1.2rem;
      background: transparent;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: var(--muted);
      font-family: 'DM Sans', sans-serif;
      font-size: 1rem;
      cursor: pointer;
      text-decoration: none;
      display: flex;
      align-items: center;
      transition: border-color .2s, color .2s;
    }
    .btn-back:hover { border-color: var(--accent); color: var(--text); }
  </style>
</head>
<body>
<div class="card">
  <a class="back-link" href="index.php">&#8592; Back to employees</a>

  <div class="top">
    <div class="avatar-lg">
      <?= strtoupper(substr($emp['firstname'],0,1).substr($emp['lastname'],0,1)) ?>
    </div>
    <div>
      <h1>Edit Employee</h1>
      <p>ID #<?= $emp['id'] ?> &mdash; <?= htmlspecialchars($emp['firstname'].' '.$emp['lastname']) ?></p>
    </div>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form action="edit.php?id=<?= $id ?>" method="post" novalidate>
    <div class="form-row">
      <div class="field">
        <label>First Name</label>
        <input type="text" name="firstname" required value="<?= htmlspecialchars($_POST['firstname'] ?? $emp['firstname']) ?>">
      </div>
      <div class="field">
        <label>Last Name</label>
        <input type="text" name="lastname" required value="<?= htmlspecialchars($_POST['lastname'] ?? $emp['lastname']) ?>">
      </div>
    </div>

    <div class="field">
      <label>Email</label>
      <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? $emp['email']) ?>">
    </div>

    <div class="field">
      <label>New Password</label>
      <input type="password" name="password" placeholder="Leave blank to keep current">
      <small>Only fill this if you want to change the password.</small>
    </div>

    <div class="field">
      <label>Gender</label>
      <div class="gender-group">
        <label class="gender-opt">
          <input type="radio" name="gender" value="male"
            <?= (($_POST['gender'] ?? $emp['gender']) === 'male') ? 'checked' : '' ?>>
          <span>Male</span>
        </label>
        <label class="gender-opt">
          <input type="radio" name="gender" value="female"
            <?= (($_POST['gender'] ?? $emp['gender']) === 'female') ? 'checked' : '' ?>>
          <span>Female</span>
        </label>
      </div>
    </div>

    <div class="btn-row">
      <a class="btn-back" href="index.php">Cancel</a>
      <button type="submit" name="submit" class="btn-submit">Save Changes</button>
    </div>
  </form>
</div>
</body>
</html>