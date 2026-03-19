<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

include 'connect.php';

$errors = [];
$values = ['firstname' => '', 'lastname' => '', 'email' => '', 'gender' => ''];

if (isset($_POST['submit'])) {

    $first_name = trim($_POST['firstname'] ?? '');
    $last_name  = trim($_POST['lastname']  ?? '');
    $password   = $_POST['password']       ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';
    $email      = trim($_POST['email']     ?? '');
    $gender     = $_POST['gender']         ?? '';

    // Keep values to refill form on error
    $values = [
        'firstname' => $first_name,
        'lastname'  => $last_name,
        'email'     => $email,
        'gender'    => $gender,
    ];

    // ── First name ──
    if (empty($first_name)) {
        $errors['firstname'] = "First name is required.";
    } elseif (!preg_match('/^[a-zA-Z\s\-\']+$/', $first_name)) {
        $errors['firstname'] = "First name can only contain letters, hyphens, or apostrophes.";
    } elseif (strlen($first_name) < 2) {
        $errors['firstname'] = "First name must be at least 2 characters.";
    } elseif (strlen($first_name) > 50) {
        $errors['firstname'] = "First name cannot exceed 50 characters.";
    }

    // ── Last name ──
    if (empty($last_name)) {
        $errors['lastname'] = "Last name is required.";
    } elseif (!preg_match('/^[a-zA-Z\s\-\']+$/', $last_name)) {
        $errors['lastname'] = "Last name can only contain letters, hyphens, or apostrophes.";
    } elseif (strlen($last_name) < 2) {
        $errors['lastname'] = "Last name must be at least 2 characters.";
    } elseif (strlen($last_name) > 50) {
        $errors['lastname'] = "Last name cannot exceed 50 characters.";
    }

    // ── Email ──
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } elseif (strlen($email) > 150) {
        $errors['email'] = "Email cannot exceed 150 characters.";
    } else {
        // Check duplicate
        $safe_email = $conn->real_escape_string($email);
        $check = $conn->query("SELECT id FROM employees WHERE email='$safe_email'");
        if ($check->num_rows > 0) {
            $errors['email'] = "This email is already registered.";
        }
    }

    // ── Password ──
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    } elseif (strlen($password) > 72) {
        $errors['password'] = "Password cannot exceed 72 characters.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = "Password must contain at least one number.";
    }

    // ── Confirm password ──
    if (empty($confirm_pw)) {
        $errors['confirm_password'] = "Please confirm your password.";
    } elseif ($password !== $confirm_pw) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    // ── Gender ──
    if (empty($gender)) {
        $errors['gender'] = "Please select a gender.";
    } elseif (!in_array($gender, ['male', 'female'])) {
        $errors['gender'] = "Invalid gender value.";
    }

    // ── If no errors, save ──
    if (empty($errors)) {
        $hashed_pw  = password_hash($password, PASSWORD_DEFAULT);
        $safe_first = $conn->real_escape_string($first_name);
        $safe_last  = $conn->real_escape_string($last_name);
        $safe_email = $conn->real_escape_string($email);
        $safe_gender= $conn->real_escape_string($gender);

        $sql = "INSERT INTO employees (firstname, lastname, password, email, gender)
                VALUES ('$safe_first','$safe_last','$hashed_pw','$safe_email','$safe_gender')";

        if ($conn->query($sql)) {
            $conn->close();
            header("Location: index.php?success=created");
            exit;
        } else {
            $errors['general'] = "Database error: " . $conn->error;
        }
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Employee — EmployeeDB</title>
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
      --gold:    #ffc46b;
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
      padding: 2rem 1.5rem;
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
      background: radial-gradient(circle, rgba(0,212,170,.08) 0%, transparent 65%);
      pointer-events: none;
    }

    .wrap {
      width: 100%;
      max-width: 520px;
      position: relative;
      z-index: 1;
    }

    /* Back link */
    .back-link {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      color: var(--dim);
      text-decoration: none;
      font-size: .85rem;
      margin-bottom: 1.5rem;
      transition: color .2s;
    }
    .back-link:hover { color: var(--accent); }

    /* Card */
    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--r);
      padding: 2.4rem;
    }

    .card-head { margin-bottom: 2rem; }
    .card-head h1 {
      font-family: 'Syne', sans-serif;
      font-size: 1.7rem;
      font-weight: 700;
      margin-bottom: .3rem;
    }
    .card-head p { color: var(--sub); font-size: .875rem; }

    /* General error */
    .alert-error {
      display: flex;
      align-items: center;
      gap: .6rem;
      background: rgba(255,107,107,.1);
      border: 1px solid rgba(255,107,107,.25);
      color: var(--coral);
      padding: .75rem 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      font-size: .875rem;
    }

    /* Form layout */
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .field { margin-bottom: 1.2rem; }
    .field label {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: .75rem;
      font-weight: 600;
      letter-spacing: .07em;
      text-transform: uppercase;
      color: var(--dim);
      margin-bottom: .5rem;
    }
    .field label .char-count {
      font-size: .72rem;
      color: var(--dim);
      font-weight: 400;
      text-transform: none;
      letter-spacing: 0;
    }

    .input-wrap { position: relative; }
    .input-wrap svg.icon {
      position: absolute;
      left: .9rem; top: 50%;
      transform: translateY(-50%);
      color: var(--dim);
      pointer-events: none;
    }

    .field input[type=text],
    .field input[type=email],
    .field input[type=password] {
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
    .field input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(108,99,255,.12);
    }
    .field input.is-error {
      border-color: var(--coral);
      box-shadow: 0 0 0 3px rgba(255,107,107,.1);
    }
    .field input.is-valid {
      border-color: var(--teal);
    }

    /* Field error message */
    .field-error {
      display: flex;
      align-items: center;
      gap: .35rem;
      color: var(--coral);
      font-size: .78rem;
      margin-top: .4rem;
    }
    .field-error svg { flex-shrink: 0; }

    /* Password strength bar */
    .strength-wrap { margin-top: .5rem; }
    .strength-bar {
      height: 3px;
      border-radius: 99px;
      background: var(--border2);
      overflow: hidden;
      margin-bottom: .3rem;
    }
    .strength-fill {
      height: 100%;
      border-radius: 99px;
      width: 0%;
      transition: width .3s, background .3s;
    }
    .strength-label { font-size: .72rem; color: var(--dim); }

    /* Password toggle */
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

    /* Gender */
    .gender-group { display: flex; gap: 1rem; margin-top: .2rem; }
    .gender-opt {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .5rem;
      padding: .65rem;
      border: 1px solid var(--border2);
      border-radius: 8px;
      cursor: pointer;
      font-size: .875rem;
      transition: border-color .2s, background .2s;
    }
    .gender-opt:has(input:checked) {
      border-color: var(--accent);
      background: rgba(108,99,255,.1);
      color: var(--accent-h);
    }
    .gender-opt input { display: none; }

    /* Submit */
    .btn-submit {
      width: 100%;
      padding: .9rem;
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-family: 'Inter', sans-serif;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: .5rem;
      transition: background .2s, transform .1s;
      letter-spacing: .01em;
    }
    .btn-submit:hover  { background: var(--accentH); }
    .btn-submit:active { transform: scale(.98); }

    /* Rules hint */
    .rules {
      background: rgba(108,99,255,.07);
      border: 1px solid rgba(108,99,255,.15);
      border-radius: 8px;
      padding: .9rem 1.1rem;
      margin-bottom: 1.5rem;
      font-size: .8rem;
      color: var(--sub);
      line-height: 1.7;
    }
    .rules strong { color: var(--accent-h); display: block; margin-bottom: .3rem; font-size: .78rem; letter-spacing: .05em; text-transform: uppercase; }

    @media (max-width: 500px) {
      .form-row { grid-template-columns: 1fr; }
      .card { padding: 1.6rem; }
    }
  </style>
</head>
<body>
<div class="wrap">

  <a class="back-link" href="index.php">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <polyline points="15 18 9 12 15 6"/>
    </svg>
    Back to employees
  </a>

  <div class="card">
    <div class="card-head">
      <h1>Add Employee</h1>
      <p>Fill in the details below to create a new record.</p>
    </div>

    <?php if (!empty($errors['general'])): ?>
    <div class="alert-error">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <?= htmlspecialchars($errors['general']) ?>
    </div>
    <?php endif; ?>

    <div class="rules">
      <strong>📋 Field Rules</strong>
      Names: letters only, 2–50 characters &nbsp;·&nbsp;
      Email: valid format, unique &nbsp;·&nbsp;
      Password: min 6 chars, 1 uppercase, 1 number
    </div>

    <form method="post" action="create.php" novalidate>

      <!-- Name row -->
      <div class="form-row">
        <div class="field">
          <label>
            First Name
            <span class="char-count" id="fn-count">0/50</span>
          </label>
          <div class="input-wrap">
            <svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <input type="text" name="firstname" id="firstname"
                   placeholder="e.g. John"
                   maxlength="50"
                   value="<?= htmlspecialchars($values['firstname']) ?>"
                   class="<?= isset($errors['firstname']) ? 'is-error' : '' ?>"
                   oninput="updateCount('firstname','fn-count',50)">
          </div>
          <?php if (isset($errors['firstname'])): ?>
          <div class="field-error">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= htmlspecialchars($errors['firstname']) ?>
          </div>
          <?php endif; ?>
        </div>

        <div class="field">
          <label>
            Last Name
            <span class="char-count" id="ln-count">0/50</span>
          </label>
          <div class="input-wrap">
            <svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <input type="text" name="lastname" id="lastname"
                   placeholder="e.g. Doe"
                   maxlength="50"
                   value="<?= htmlspecialchars($values['lastname']) ?>"
                   class="<?= isset($errors['lastname']) ? 'is-error' : '' ?>"
                   oninput="updateCount('lastname','ln-count',50)">
          </div>
          <?php if (isset($errors['lastname'])): ?>
          <div class="field-error">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= htmlspecialchars($errors['lastname']) ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Email -->
      <div class="field">
        <label>Email</label>
        <div class="input-wrap">
          <svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
          <input type="email" name="email"
                 placeholder="employee@example.com"
                 maxlength="150"
                 value="<?= htmlspecialchars($values['email']) ?>"
                 class="<?= isset($errors['email']) ? 'is-error' : '' ?>">
        </div>
        <?php if (isset($errors['email'])): ?>
        <div class="field-error">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?= htmlspecialchars($errors['email']) ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Password -->
      <div class="field">
        <label>Password</label>
        <div class="input-wrap">
          <svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <input type="password" name="password" id="pw"
                 placeholder="Min 6 chars, 1 uppercase, 1 number"
                 maxlength="72"
                 class="<?= isset($errors['password']) ? 'is-error' : '' ?>"
                 oninput="checkStrength(this.value)">
          <button type="button" class="toggle-pw" onclick="togglePw('pw','eye1')">
            <svg id="eye1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
        <div class="strength-wrap">
          <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
          <span class="strength-label" id="strength-label">Enter a password</span>
        </div>
        <?php if (isset($errors['password'])): ?>
        <div class="field-error">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?= htmlspecialchars($errors['password']) ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Confirm Password -->
      <div class="field">
        <label>Confirm Password</label>
        <div class="input-wrap">
          <svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <input type="password" name="confirm_password" id="cpw"
                 placeholder="Re-enter your password"
                 maxlength="72"
                 class="<?= isset($errors['confirm_password']) ? 'is-error' : '' ?>">
          <button type="button" class="toggle-pw" onclick="togglePw('cpw','eye2')">
            <svg id="eye2" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
        <?php if (isset($errors['confirm_password'])): ?>
        <div class="field-error">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?= htmlspecialchars($errors['confirm_password']) ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Gender -->
      <div class="field">
        <label>Gender</label>
        <div class="gender-group">
          <label class="gender-opt">
            <input type="radio" name="gender" value="male"
              <?= $values['gender'] === 'male' ? 'checked' : '' ?>>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="4"/><line x1="16" y1="8" x2="22" y2="2"/>
              <polyline points="17 2 22 2 22 7"/>
            </svg>
            Male
          </label>
          <label class="gender-opt">
            <input type="radio" name="gender" value="female"
              <?= $values['gender'] === 'female' ? 'checked' : '' ?>>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="8" r="4"/><line x1="12" y1="12" x2="12" y2="22"/>
              <line x1="8" y1="18" x2="16" y2="18"/>
            </svg>
            Female
          </label>
        </div>
        <?php if (isset($errors['gender'])): ?>
        <div class="field-error" style="margin-top:.5rem">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?= htmlspecialchars($errors['gender']) ?>
        </div>
        <?php endif; ?>
      </div>

      <button type="submit" name="submit" class="btn-submit">
        Create Employee
      </button>
    </form>
  </div>
</div>

<script>
// Character counter
function updateCount(inputId, countId, max) {
  const len = document.getElementById(inputId).value.length;
  const el  = document.getElementById(countId);
  el.textContent = len + '/' + max;
  el.style.color = len > max * 0.9 ? '#ff6b6b' : '';
}

// Initialize counts on page load
window.addEventListener('load', () => {
  updateCount('firstname', 'fn-count', 50);
  updateCount('lastname',  'ln-count', 50);
});

// Password strength checker
function checkStrength(pw) {
  const fill  = document.getElementById('strength-fill');
  const label = document.getElementById('strength-label');
  let score = 0;

  if (pw.length >= 6)                    score++;
  if (pw.length >= 10)                   score++;
  if (/[A-Z]/.test(pw))                  score++;
  if (/[0-9]/.test(pw))                  score++;
  if (/[^A-Za-z0-9]/.test(pw))          score++;

  const levels = [
    { pct: '0%',   color: '',              text: 'Enter a password' },
    { pct: '20%',  color: '#ff6b6b',       text: 'Very weak' },
    { pct: '40%',  color: '#ff9a4d',       text: 'Weak' },
    { pct: '60%',  color: '#ffc46b',       text: 'Fair' },
    { pct: '80%',  color: '#6aabf7',       text: 'Strong' },
    { pct: '100%', color: '#00d4aa',       text: 'Very strong ✓' },
  ];

  const lvl = pw.length === 0 ? levels[0] : levels[Math.min(score, 5)];
  fill.style.width      = lvl.pct;
  fill.style.background = lvl.color;
  label.textContent     = lvl.text;
  label.style.color     = lvl.color || 'var(--dim)';
}

// Show/hide password toggle
function togglePw(inputId, iconId) {
  const input = document.getElementById(inputId);
  const ico   = document.getElementById(iconId);
  if (input.type === 'password') {
    input.type = 'text';
    ico.innerHTML = `
      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
      <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
      <line x1="1" y1="1" x2="23" y2="23"/>`;
  } else {
    input.type = 'password';
    ico.innerHTML = `
      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
      <circle cx="12" cy="12" r="3"/>`;
  }
}
</script>
</body>
</html>