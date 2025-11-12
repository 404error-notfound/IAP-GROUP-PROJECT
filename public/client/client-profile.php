<?php
// client-profile.php
// Shows logged-in user's registration details and provides a dark-mode toggle.

// Start session and ensure user is authenticated
session_start();

if (!isset($_SESSION['user'])) {
    // Not logged in — send to login page (relative to public/client/)
    header('Location: ../login.php');
    exit;
}

$user = $_SESSION['user'];

function e($v) {
    return htmlspecialchars($v ?? '—', ENT_QUOTES, 'UTF-8');
}

// Simple helper to format a field if present
function showField($key, $label, $user) {
    $val = $user[$key] ?? null;
    return '<div class="field"><div class="label">'.e($label).'</div><div class="value">'.e($val).'</div></div>';
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>My Profile — GoPuppyGo</title>
    <link rel="icon" href="/IAP-GROUP-PROJECT/public/images/gopuppygo-logo.svg" type="image/svg+xml">
    <style>
        :root{
            --bg: #f5f7fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --accent: #2563eb;
            --accent-2: #059669;
        }
        .dark-mode{
            --bg: #0b1220;
            --card: #0f1724;
            --text: #e6eef8;
            --muted: #98a2b3;
            --accent: #60a5fa;
            --accent-2: #34d399;
        }
        html,body{height:100%;}
        body{margin:0;font-family:Segoe UI, Roboto, Helvetica, Arial, sans-serif;background:var(--bg);color:var(--text);}
        .wrap{max-width:980px;margin:28px auto;padding:20px;}
        .top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px}
        .title{display:flex;gap:12px;align-items:center}
        .logo-badge{width:56px;height:56px;border-radius:10px;background:var(--card);display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.06)}
        .logo-badge img{width:44px;height:44px}
        h1{font-size:20px;margin:0}
        p.sub{margin:0;color:var(--muted)}
        .controls{display:flex;gap:12px;align-items:center}
        .btn{background:var(--accent);color:white;padding:8px 12px;border-radius:8px;border:0;cursor:pointer;font-weight:600}
        .btn.ghost{background:transparent;color:var(--accent);box-shadow:none;border:1px solid rgba(255,255,255,0.04)}

        .profile-grid{display:grid;grid-template-columns:260px 1fr;gap:18px}
        .card{background:var(--card);padding:18px;border-radius:12px;box-shadow:0 6px 18px rgba(2,6,23,0.06)}
        .avatar{width:120px;height:120px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent-2));display:flex;align-items:center;justify-content:center;color:white;font-size:32px;font-weight:700}
        .meta{margin-top:14px}
        .meta .row{display:flex;gap:6px;align-items:center}
        .field{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px dashed rgba(0,0,0,0.04)}
        .field .label{color:var(--muted);min-width:160px}
        .field .value{font-weight:600;text-align:right}

        @media (max-width:720px){
            .profile-grid{grid-template-columns:1fr;}
            .field .label{min-width:120px}
        }
    </style>
</head>
<body>
<div class="wrap" id="page">
    <div class="top">
        <div class="title">
            <div class="logo-badge" aria-hidden="true">
                <img src="/IAP-GROUP-PROJECT/public/images/gopuppygo-logo.svg" alt="logo">
            </div>
            <div>
                <h1>My Profile</h1>
                <p class="sub">View your registration details and account settings</p>
            </div>
        </div>

        <div class="controls">
            <button id="darkToggle" class="btn" aria-pressed="false">Enable Dark Mode</button>
            <a href="../client/client-dashboard.php" class="btn ghost">Back to Dashboard</a>
        </div>
    </div>

    <div class="profile-grid">
        <aside>
            <div class="card" style="text-align:center">
                <div class="avatar">
                    <?php
                    // Use initials from name
                    $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                    if ($name === '') {
                        echo 'U';
                    } else {
                        $parts = preg_split('/\s+/', $name);
                        $initials = strtoupper((substr($parts[0],0,1) . (isset($parts[1]) ? substr($parts[1],0,1) : '')));
                        echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8');
                    }
                    ?>
                </div>
                <div class="meta">
                    <div class="row"><strong><?php echo e($user['first_name'] ?? $user['email'] ?? 'User'); ?></strong></div>
                    <div class="row"><small class="sub">Member since <?php echo e(isset($user['created_at']) ? date('M j, Y', strtotime($user['created_at'])) : '—'); ?></small></div>
                </div>
            </div>
        </aside>

        <main>
            <div class="card">
                <h3 style="margin-top:0">Registration details</h3>
                <?php
                echo showField('email', 'Email', $user);
                echo showField('contact_email', 'Contact Email', $user);
                echo showField('first_name', 'First name', $user);
                echo showField('last_name', 'Last name', $user);
                echo showField('phone', 'Phone', $user);
                echo showField('address', 'Address', $user);
                echo showField('role', 'Role', $user);
                echo showField('gender', 'Gender', $user);
                echo showField('created_at', 'Account created', $user);
                ?>

                <div style="margin-top:14px;display:flex;gap:8px;justify-content:flex-end">
                    <a href="edit-profile.php" class="btn">Edit profile</a>
                    <a href="../logout.php" class="btn ghost">Log out</a>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Dark mode toggle persisted in localStorage
(function(){
    const root = document.documentElement;
    const btn = document.getElementById('darkToggle');
    const storageKey = 'gopuppygo_dark';

    function apply(pref){
        if(pref === '1'){
            root.classList.add('dark-mode');
            btn.textContent = 'Disable Dark Mode';
            btn.setAttribute('aria-pressed','true');
        } else {
            root.classList.remove('dark-mode');
            btn.textContent = 'Enable Dark Mode';
            btn.setAttribute('aria-pressed','false');
        }
    }

    // initialize from storage or prefer system dark
    const saved = localStorage.getItem(storageKey);
    if(saved === null){
        // if user prefers dark via OS, default on
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        apply(prefersDark ? '1' : '0');
    } else {
        apply(saved);
    }

    btn.addEventListener('click', function(){
        const cur = root.classList.contains('dark-mode') ? '1' : '0';
        const next = cur === '1' ? '0' : '1';
        localStorage.setItem(storageKey, next);
        apply(next);
    });
})();
</script>
</body>
</html>
