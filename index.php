<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$logged_in_name = $_SESSION['user_name'];

include 'connect.php';

$search = '';
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string(trim($_GET['search']));
    $sql = "SELECT * FROM employees WHERE firstname LIKE '%$search%'
            OR lastname LIKE '%$search%' OR email LIKE '%$search%'
            ORDER BY id ASC";
} else {
    $sql = "SELECT * FROM employees ORDER BY id ASC";
}

$result    = $conn->query($sql);
$employees = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}
$conn->close();

$success = $_GET['success'] ?? '';
$msg = match($success) {
    'created' => 'Employee created successfully!',
    'updated' => 'Employee updated successfully!',
    'deleted' => 'Employee deleted.',
    'welcome' => 'Welcome back, ' . htmlspecialchars($logged_in_name) . '!',
    default   => ''
};

$total   = count($employees);
$males   = count(array_filter($employees, fn($e) => strtolower($e['gender']) === 'male'));
$females = $total - $males;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Manager</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:       #080810;
      --panel:    #0f0f1a;
      --card:     #13131f;
      --border:   #1e1e30;
      --border2:  #252538;
      --accent:   #6c63ff;
      --accent-h: #8b84ff;
      --teal:     #00d4aa;
      --coral:    #ff6b6b;
      --gold:     #ffc46b;
      --text:     #eeeef8;
      --sub:      #9898c0;
      --dim:      #5a5a80;
      --r:        12px;
    }

    html { scroll-behavior: smooth; }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
    }

    body::before, body::after {
      content: '';
      position: fixed;
      border-radius: 50%;
      pointer-events: none;
      z-index: 0;
    }
    body::before {
      width: 700px; height: 700px;
      top: -200px; left: -150px;
      background: radial-gradient(circle, rgba(108,99,255,.13) 0%, transparent 65%);
    }
    body::after {
      width: 500px; height: 500px;
      bottom: -150px; right: -100px;
      background: radial-gradient(circle, rgba(0,212,170,.09) 0%, transparent 65%);
    }

    /* ── Topbar ── */
    .topbar {
      position: sticky; top: 0; z-index: 50;
      background: rgba(8,8,16,.88);
      backdrop-filter: blur(18px);
      border-bottom: 1px solid var(--border);
      padding: .9rem 2.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .logo { display: flex; align-items: center; gap: .7rem; }
    .logo-icon {
      width: 34px; height: 34px;
      background: linear-gradient(135deg, var(--accent), var(--teal));
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
    }
    .logo-text { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 1rem; }
    .logo-text span { color: var(--accent); }

    .btn-add {
      display: inline-flex; align-items: center; gap: .45rem;
      background: var(--accent);
      color: #fff;
      text-decoration: none;
      font-weight: 600;
      font-size: .85rem;
      padding: .55rem 1.15rem;
      border-radius: 8px;
      transition: background .2s, transform .1s;
    }
    .btn-add:hover  { background: var(--accent-h); }
    .btn-add:active { transform: scale(.97); }

    /* ── Page ── */
    .page {
      max-width: 1100px;
      margin: 0 auto;
      padding: 2.5rem 2rem 5rem;
      position: relative; z-index: 1;
    }

    .page-head { margin-bottom: 2rem; }
    .page-head h1 {
      font-family: 'Syne', sans-serif;
      font-size: 2.6rem;
      font-weight: 800;
      letter-spacing: -.02em;
      line-height: 1;
    }
    .page-head h1 span {
      background: linear-gradient(90deg, var(--accent), var(--teal));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .page-head p { color: var(--sub); margin-top: .5rem; font-size: .9rem; }

    /* ── Toast ── */
    .toast {
      display: flex; align-items: center; gap: .6rem;
      background: rgba(0,212,170,.1);
      border: 1px solid rgba(0,212,170,.25);
      color: var(--teal);
      padding: .75rem 1.1rem;
      border-radius: 8px;
      margin-bottom: 1.8rem;
      font-size: .875rem;
      animation: slideIn .35s ease;
    }
    @keyframes slideIn {
      from { opacity:0; transform: translateY(-8px); }
      to   { opacity:1; transform: none; }
    }

    /* ── Stats ── */
    .stats {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1rem;
      margin-bottom: 1.8rem;
    }
    .stat {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--r);
      padding: 1.2rem 1.5rem;
      position: relative;
      overflow: hidden;
    }
    .stat::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0; height: 2px;
    }
    .stat:nth-child(1)::before { background: linear-gradient(90deg, var(--accent), var(--teal)); }
    .stat:nth-child(2)::before { background: linear-gradient(90deg, #6aabf7, var(--accent)); }
    .stat:nth-child(3)::before { background: linear-gradient(90deg, var(--coral), var(--gold)); }
    .stat-label {
      font-size: .72rem; font-weight: 600;
      letter-spacing: .07em; text-transform: uppercase;
      color: var(--dim); margin-bottom: .4rem;
    }
    .stat-value {
      font-family: 'Syne', sans-serif;
      font-size: 2rem; font-weight: 700;
    }

    /* ── Toolbar ── */
    .toolbar {
      display: flex; gap: .8rem; margin-bottom: 1.4rem; align-items: center;
    }
    .search-wrap { flex: 1; position: relative; }
    .search-wrap svg {
      position: absolute; left: .9rem; top: 50%;
      transform: translateY(-50%);
      color: var(--dim); pointer-events: none;
    }
    .search-wrap input {
      width: 100%;
      background: var(--card);
      border: 1px solid var(--border2);
      border-radius: 8px;
      color: var(--text);
      font-family: 'Inter', sans-serif;
      font-size: .9rem;
      padding: .65rem .9rem .65rem 2.5rem;
      outline: none;
      transition: border-color .2s;
    }
    .search-wrap input::placeholder { color: var(--dim); }
    .search-wrap input:focus { border-color: var(--accent); }

    .btn-search {
      padding: .65rem 1.2rem;
      background: var(--card);
      border: 1px solid var(--border2);
      border-radius: 8px;
      color: var(--text);
      font-family: 'Inter', sans-serif;
      font-size: .875rem;
      cursor: pointer;
      transition: border-color .2s;
    }
    .btn-search:hover { border-color: var(--accent); }

    .btn-clear {
      padding: .65rem .9rem;
      background: transparent;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: var(--dim);
      font-size: .875rem;
      text-decoration: none;
      transition: color .2s, border-color .2s;
    }
    .btn-clear:hover { color: var(--text); border-color: var(--border2); }

    /* ── Table ── */
    .table-wrap {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--r);
      overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; }

    thead {
      background: var(--panel);
      border-bottom: 1px solid var(--border2);
    }
    thead th {
      text-align: left;
      padding: .9rem 1.3rem;
      font-size: .72rem; font-weight: 600;
      letter-spacing: .08em; text-transform: uppercase;
      color: var(--dim); white-space: nowrap;
    }

    tbody tr {
      border-bottom: 1px solid var(--border);
      transition: background .15s;
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: rgba(108,99,255,.045); }

    tbody td {
      padding: .95rem 1.3rem;
      font-size: .9rem;
      vertical-align: middle;
    }

    .td-id    { color: var(--dim); font-size: .8rem; font-family: 'Syne', sans-serif; font-weight: 600; }
    .td-name  { font-weight: 500; }
    .td-email { color: var(--sub); font-size: .875rem; }

    /* Gender badge */
    .badge {
      display: inline-flex; align-items: center; gap: .35rem;
      padding: .25rem .75rem;
      border-radius: 99px;
      font-size: .75rem; font-weight: 600;
      text-transform: capitalize;
    }
    .badge-dot { width: 6px; height: 6px; border-radius: 50%; }
    .badge-male   { background: rgba(106,171,247,.12); color: #6aabf7; }
    .badge-male   .badge-dot { background: #6aabf7; }
    .badge-female { background: rgba(255,107,107,.12); color: #ff9eb0; }
    .badge-female .badge-dot { background: #ff9eb0; }

    /* Action buttons */
    .actions { display: flex; gap: .5rem; }
    .btn-edit, .btn-del {
      display: inline-flex; align-items: center; gap: .3rem;
      padding: .35rem .85rem;
      border-radius: 6px;
      font-family: 'Inter', sans-serif;
      font-size: .78rem; font-weight: 600;
      cursor: pointer; text-decoration: none; border: none;
      transition: opacity .2s, transform .1s;
    }
    .btn-edit {
      background: rgba(108,99,255,.18); color: var(--accent-h);
      border: 1px solid rgba(108,99,255,.25);
    }
    .btn-del {
      background: rgba(255,107,107,.12); color: var(--coral);
      border: 1px solid rgba(255,107,107,.2);
    }
    .btn-edit:hover, .btn-del:hover  { opacity: .75; }
    .btn-edit:active, .btn-del:active { transform: scale(.95); }

    /* Empty state */
    .empty-state { text-align: center; padding: 5rem 2rem; color: var(--dim); }
    .empty-state svg { margin-bottom: 1rem; opacity: .3; }
    .empty-state p { font-size: .9rem; }

    /* ── Delete modal ── */
    .overlay {
      position: fixed; inset: 0;
      background: rgba(0,0,0,.72);
      backdrop-filter: blur(6px);
      display: flex; align-items: center; justify-content: center;
      z-index: 200;
      opacity: 0; pointer-events: none;
      transition: opacity .25s;
    }
    .overlay.open { opacity: 1; pointer-events: all; }

    .modal {
      background: var(--card);
      border: 1px solid var(--border2);
      border-radius: 16px;
      padding: 2rem;
      width: 90%; max-width: 360px;
      transform: scale(.93) translateY(10px);
      transition: transform .25s;
    }
    .overlay.open .modal { transform: scale(1) translateY(0); }

    .modal-icon {
      width: 48px; height: 48px;
      background: rgba(255,107,107,.12);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 1.2rem;
    }
    .modal h2 {
      font-family: 'Syne', sans-serif;
      font-size: 1.3rem; margin-bottom: .4rem;
    }
    .modal p { color: var(--sub); font-size: .875rem; line-height: 1.5; margin-bottom: 1.5rem; }
    .modal-btns { display: flex; gap: .7rem; }
    .btn-cancel {
      flex: 1; padding: .75rem;
      background: transparent;
      border: 1px solid var(--border2);
      border-radius: 8px; color: var(--sub);
      font-family: 'Inter', sans-serif; font-size: .9rem; cursor: pointer;
      transition: color .2s, border-color .2s;
    }
    .btn-cancel:hover { color: var(--text); }
    .btn-confirm {
      flex: 1; padding: .75rem;
      background: var(--coral); border: none;
      border-radius: 8px; color: #fff;
      font-family: 'Inter', sans-serif; font-size: .9rem; font-weight: 600;
      cursor: pointer; transition: opacity .2s;
    }
    .btn-confirm:hover { opacity: .85; }

    @media (max-width: 700px) {
      .topbar { padding: .8rem 1.2rem; }
      .page   { padding: 1.5rem 1rem 3rem; }
      .page-head h1 { font-size: 1.9rem; }
      .stats  { grid-template-columns: 1fr; }
      .table-wrap { overflow-x: auto; }
      table   { min-width: 580px; }
    }
  </style>
</head>
<body>

<header class="topbar">
  <div class="logo">
    <div class="logo-icon">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
    </div>
    <span class="logo-text">Employee<span>DB</span></span>
  </div>
  <div style="display:flex;align-items:center;gap:1rem;">
    <span style="color:var(--sub);font-size:.85rem;">
      👋 <?= htmlspecialchars($logged_in_name) ?>
    </span>
    <a class="btn-add" href="create.php">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
      <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    Add Employee
  </a>
  <a href="logout.php" style="
    display:inline-flex;align-items:center;gap:.4rem;
    padding:.55rem 1rem;
    background:rgba(255,107,107,.12);
    border:1px solid rgba(255,107,107,.2);
    border-radius:8px;
    color:#ff6b6b;
    font-size:.85rem;font-weight:600;
    text-decoration:none;
    transition:opacity .2s;">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
      <polyline points="16 17 21 12 16 7"/>
      <line x1="21" y1="12" x2="9" y2="12"/>
    </svg>
    Logout
  </a>
  </div>
</header>

<div class="page">

  <div class="page-head">
    <h1>All <span>Employees</span></h1>
    <p>Manage your workforce — <?= $total ?> record<?= $total !== 1 ? 's' : '' ?> in the system</p>
  </div>

  <?php if ($msg): ?>
  <div class="toast" id="toast">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <polyline points="20 6 9 17 4 12"/>
    </svg>
    <?= htmlspecialchars($msg) ?>
  </div>
  <?php endif; ?>

  <div class="stats">
    <div class="stat">
      <div class="stat-label">Total Employees</div>
      <div class="stat-value"><?= $total ?></div>
    </div>
    <div class="stat">
      <div class="stat-label">Male</div>
      <div class="stat-value"><?= $males ?></div>
    </div>
    <div class="stat">
      <div class="stat-label">Female</div>
      <div class="stat-value"><?= $females ?></div>
    </div>
  </div>

  <form class="toolbar" method="get" action="index.php">
    <div class="search-wrap">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input type="text" name="search" placeholder="Search by name or email…"
             value="<?= htmlspecialchars($search) ?>">
    </div>
    <button type="submit" class="btn-search">Search</button>
    <?php if ($search): ?>
      <a class="btn-clear" href="index.php">✕ Clear</a>
    <?php endif; ?>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Email</th>
          <th>Gender</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($employees)): ?>
        <tr>
          <td colspan="6">
            <div class="empty-state">
              <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                <circle cx="12" cy="8" r="4"/>
                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
              </svg>
              <p>No employees found<?= $search ? ' for "'.htmlspecialchars($search).'"' : '' ?>.</p>
            </div>
          </td>
        </tr>
        <?php else: ?>
          <?php foreach ($employees as $emp): ?>
          <tr>
            <td class="td-id"><?= $emp['id'] ?></td>
            <td class="td-name"><?= htmlspecialchars($emp['firstname']) ?></td>
            <td class="td-name"><?= htmlspecialchars($emp['lastname']) ?></td>
            <td class="td-email"><?= htmlspecialchars($emp['email']) ?></td>
            <td>
              <?php $g = strtolower($emp['gender']); ?>
              <span class="badge badge-<?= $g ?>">
                <span class="badge-dot"></span>
                <?= ucfirst($emp['gender']) ?>
              </span>
            </td>
            <td>
              <div class="actions">
                <a class="btn-edit" href="edit.php?id=<?= $emp['id'] ?>">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4z"/>
                  </svg>
                  Edit
                </a>
                <button class="btn-del"
                  onclick="openDelete(<?= $emp['id'] ?>, '<?= htmlspecialchars($emp['firstname'].' '.$emp['lastname'], ENT_QUOTES) ?>')">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                  </svg>
                  Delete
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Delete confirmation modal -->
<div class="overlay" id="overlay">
  <div class="modal">
    <div class="modal-icon">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ff6b6b" stroke-width="2">
        <polyline points="3 6 5 6 21 6"/>
        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
        <path d="M10 11v6M14 11v6M9 6V4h6v2"/>
      </svg>
    </div>
    <h2>Delete Employee?</h2>
    <p id="modal-msg">This will permanently remove the employee from the system.</p>
    <div class="modal-btns">
      <button class="btn-cancel" onclick="closeDelete()">Cancel</button>
      <form method="post" action="delete.php" style="flex:1;display:flex;">
        <input type="hidden" name="id" id="del-id">
        <button type="submit" class="btn-confirm" style="width:100%">Yes, Delete</button>
      </form>
    </div>
  </div>
</div>

<script>
  function openDelete(id, name) {
    document.getElementById('del-id').value = id;
    document.getElementById('modal-msg').textContent =
      'Remove "' + name + '" from the system? This cannot be undone.';
    document.getElementById('overlay').classList.add('open');
  }
  function closeDelete() {
    document.getElementById('overlay').classList.remove('open');
  }
  document.getElementById('overlay').addEventListener('click', function(e) {
    if (e.target === this) closeDelete();
  });

  const toast = document.getElementById('toast');
  if (toast) setTimeout(() => {
    toast.style.transition = 'opacity .4s';
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 400);
  }, 3500);
</script>
</body>
</html>