<?php
session_start();

// Already logged in? Go to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

include 'connect.php';

$error   = '';
$success = '';

// Success toast after registration
if (isset($_GET['success']) && $_GET['success'] === 'registered') {
    $success = "Account created! You can now sign in.";
}

if (isset($_POST['submit'])) {
    $email    = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $result = $conn->query("SELECT * FROM employees WHERE email='$email' LIMIT 1");

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
                $_SESSION['user_email']= $user['email'];
                $_SESSION['user_role'] = $user['role'];

                $conn->close();
                header("Location: index.php?success=welcome");
                exit;
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with that email.";
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
  <title>Login — EmployeeDB</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:      #080810;
      --card:    #13131f;
      --border:  #1e1e30;
      --border2: #252538;
      --accent:  #6c63ff;
      --accentH: #8b84ff;
      --teal:    #00d4aa;
      --coral:   #ff6b6b;
      --text:    #eeeef8;
      --sub:     #9898c0;
      --dim:     #5a5a80;
      --r:       14px;
    }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }

    body::before {
      content: '';
      position: fixed;
      width: 700px; height: 700px;
      top: -200px; left: -200px;
      background: radial-gradient(circle, rgba(108,99,255,.15) 0%, transparent 65%);
      pointer-events: none;
    }
    body::after {
      content: '';
      position: fixed;
      width: 500px; height: 500px;
      bottom: -150px; right: -100px;
      background: radial-gradient(circle, rgba(0,212,170,.1) 0%, transparent 65%);
      pointer-events: none;
    }

    .wrap {
      width: 100%;
      max-width: 420px;
      position: relative;
      z-index: 1;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: .7rem;
      justify-content: center;
      margin-bottom: 2rem;
    }
    .brand-icon {
      width: 40px; height: 40px;
      background: linear-gradient(135deg, var(--accent), var(--teal));
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
    }
    .brand-name { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 1.15rem; }
    .brand-name span { color: var(--accent); }

    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--r);
      padding: 2.4rem;
    }

    .card-head { margin-bottom: 1.8rem; }
    .card-head h1 { font-family: 'Syne', sans-serif; font-size: 1.7rem; font-weight: 700; margin-bottom: .3rem; }
    .card-head p  { color: var(--sub); font-size: .875rem; }

    /* Success toast */
    .alert-success {
      display: flex;
      align-items: center;
      gap: .6rem;
      background: rgba(0,212,170,.1);
      border: 1px solid rgba(0,212,170,.25);
      color: var(--teal);
      padding: .75rem 1rem;
      border-radius: 8px;
      margin-bottom: 1.4rem;
      font-size: .875rem;
      animation: slideIn .35s ease;
    }
    @keyframes slideIn {
      from { opacity:0; transform: translateY(-6px); }
      to   { opacity:1; transform: none; }
    }

    /* Error alert */
    .alert-error {
      display: flex;
      align-items: center;
      gap: .6rem;
      background: rgba(255,107,107,.1);
      border: 1px solid rgba(255,107,107,.25);
      color: var(--coral);
      padding: .75rem 1rem;
      border-radius: 8px;
      margin-bottom: 1.4rem;
      font-size: .875rem;
      animation: shake .35s ease;
    }
    @keyframes shake {
      0%,100% { transform: translateX(0); }
      25%      { transform: translateX(-6px); }
      75%      { transform: translateX(6px); }
    }

    .field { margin-bottom: 1.2rem; }
    .field label {
      display: block;
      font-size: .75rem;
      font-weight: 600;
      letter-spacing: .07em;
      text-transform: uppercase;
      color: var(--dim);
      margin-bottom: .5rem;
    }

    .input-wrap { position: relative; }
    .input-wrap svg.icon {
      position: absolute;
      left: .9rem; top: 50%;
      transform: translateY(-50%);
      color: var(--dim);
      pointer-events: none;
    }

    .field input {
      width: 100%;
      background: var(--bg);
      border: 1px solid var(--border2);
      border-radius: 8px;
      color: var(--text);
      font-family: 'Inter', sans-serif;
      font-size: .95rem;
      padding: .75rem .9rem .75rem 2.6rem;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }
    .field input::placeholder { color: var(--dim); }
    .field input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(108,99,255,.12); }

    .toggle-pw {
      position: absolute;
      right: .9rem; top: 50%;
      transform: translateY(-50%);
      background: none; border: none;
      color: var(--dim); cursor: pointer;
      padding: 0; line-height: 0;
      transition: color .2s;
    }
    .toggle-pw:hover { color: var(--sub); }

    .btn-login {
      width: 100%;
      padding: .85rem;
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-family: 'Inter', sans-serif;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: .4rem;
      transition: background .2s, transform .1s;
    }
    .btn-login:hover  { background: var(--accentH); }
    .btn-login:active { transform: scale(.98); }

    .divider {
      text-align: center;
      color: var(--dim);
      font-size: .8rem;
      margin: 1.4rem 0;
      position: relative;
    }
    .divider::before, .divider::after {
      content: '';
      position: absolute;
      top: 50%; width: 38%;
      height: 1px;
      background: var(--border2);
    }
    .divider::before { left: 0; }
    .divider::after  { right: 0; }

    .register-link {
      display: block;
      text-align: center;
      font-size: .875rem;
      color: var(--sub);
    }
    .register-link a { color: var(--accent); text-decoration: none; font-weight: 500; }
    .register-link a:hover { color: var(--accentH); }
  </style>
</head>
<body>
<div class="wrap">

  <div class="brand">
    <div class="brand-icon">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
    </div>
    <span class="brand-name">Employee<span>DB</span></span>
  </div>

  <div class="card">
    <div class="card-head">
      <h1>Welcome back</h1>
      <p>Sign in to access the employee system</p>
    </div>

    <?php if ($success): ?>
    <div class="alert-success" id="success-toast">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
      <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert-error">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="post" action="login.php" novalidate>

      <div class="field">
        <label>Email</label>
        <div class="input-wrap">
          <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
          <input type="email" name="email" placeholder="you@example.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
      </div>

      <div class="field">
        <label>Password</label>
        <div class="input-wrap">
          <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <input type="password" name="password" id="pw" placeholder="••••••••" required>
          <button type="button" class="toggle-pw" onclick="togglePw()">
            <svg id="eye-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
      </div>

      <button type="submit" name="submit" class="btn-login">Sign In</button>
    </form>

    <div class="divider">or</div>
    <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
  </div>
</div>

<script>
function togglePw() {
  const pw  = document.getElementById('pw');
  const ico = document.getElementById('eye-icon');
  if (pw.type === 'password') {
    pw.type = 'text';
    ico.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`;
  } else {
    pw.type = 'password';
    ico.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
  }
}

// Auto-hide success toast
const toast = document.getElementById('success-toast');
if (toast) setTimeout(() => {
  toast.style.transition = 'opacity .4s';
  toast.style.opacity = '0';
  setTimeout(() => toast.remove(), 400);
}, 4000);
</script>
</body>
</html>