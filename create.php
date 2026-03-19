<?php
include 'connect.php';

if (isset($_POST['submit'])) {
    $first_name = $conn->real_escape_string(trim($_POST['firstname']));
    $last_name  = $conn->real_escape_string(trim($_POST['lastname']));
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email      = $conn->real_escape_string(trim($_POST['email']));
    $gender     = $conn->real_escape_string($_POST['gender']);

    // Check duplicate email
    $check = $conn->query("SELECT id FROM employees WHERE email='$email'");
    if ($check->num_rows > 0) {
        $error = "Email already registered.";
    } else {
        $sql    = "INSERT INTO employees (firstname, lastname, password, email, gender)
                   VALUES ('$first_name','$last_name','$password','$email','$gender')";
        $result = $conn->query($sql);
        if ($result) {
            header("Location: index.php?success=created");
            exit;
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Employee</title>
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
      --success: #6af7a2;
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
      top: -40%;
      right: -20%;
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(124,106,247,.18) 0%, transparent 70%);
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

    h1 {
      font-family: 'DM Serif Display', serif;
      font-size: 1.9rem;
      margin-bottom: .4rem;
    }
    .subtitle { color: var(--muted); font-size: .9rem; margin-bottom: 2rem; }

    .alert {
      padding: .75rem 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      font-size: .875rem;
    }
    .alert-error  { background: rgba(247,106,106,.12); border: 1px solid rgba(247,106,106,.3); color: var(--danger); }

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

    .gender-group { display: flex; gap: 1.2rem; padding-top: .3rem; }
    .gender-opt { display: flex; align-items: center; gap: .5rem; cursor: pointer; }
    .gender-opt input { accent-color: var(--accent); width: 16px; height: 16px; cursor: pointer; }
    .gender-opt span { font-size: .95rem; }

    .btn-submit {
      width: 100%;
      padding: .85rem;
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-family: 'DM Sans', sans-serif;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: .5rem;
      transition: opacity .2s, transform .1s;
    }
    .btn-submit:hover { opacity: .85; }
    .btn-submit:active { transform: scale(.98); }
  </style>
</head>
<body>
<div class="card">
  <a class="back-link" href="index.php">&#8592; Back to employees</a>
  <h1>Add Employee</h1>
  <p class="subtitle">Fill in the details to create a new record.</p>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form action="create.php" method="post" novalidate>
    <div class="form-row">
      <div class="field">
        <label>First Name</label>
        <input type="text" name="firstname" required value="<?= htmlspecialchars($_POST['firstname'] ?? '') ?>">
      </div>
      <div class="field">
        <label>Last Name</label>
        <input type="text" name="lastname" required value="<?= htmlspecialchars($_POST['lastname'] ?? '') ?>">
      </div>
    </div>

    <div class="field">
      <label>Email</label>
      <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>

    <div class="field">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>

    <div class="field">
      <label>Gender</label>
      <div class="gender-group">
        <label class="gender-opt">
          <input type="radio" name="gender" value="male" <?= (($_POST['gender'] ?? '') === 'male') ? 'checked' : '' ?>>
          <span>Male</span>
        </label>
        <label class="gender-opt">
          <input type="radio" name="gender" value="female" <?= (($_POST['gender'] ?? '') === 'female') ? 'checked' : '' ?>>
          <span>Female</span>
        </label>
      </div>
    </div>

    <button type="submit" name="submit" class="btn-submit">Create Employee</button>
  </form>
</div>
</body>
</html>