<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — <?= esc($nama_warkop) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
</head>
<body class="login-page">

<div class="login-wrapper reveal">

    <!-- LEFT: Illustration -->
    <div class="login-illustration">
        <img class="login-brand-logo"
             src="<?= base_url($logo) ?>"
             alt="Logo Lower Manhattan Coffee Shop">

        <!-- Coffee cup SVG with steam -->
        <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Steam -->
            <path d="M40 38 Q42 28 40 18" stroke="#D9A99A" stroke-width="2" stroke-linecap="round" opacity=".6" style="animation: steam 2.5s ease-in-out infinite"/>
            <path d="M55 35 Q57 22 55 12" stroke="#D9A99A" stroke-width="2" stroke-linecap="round" opacity=".5" style="animation: steam 3s ease-in-out infinite .5s"/>
            <path d="M70 38 Q72 28 70 18" stroke="#D9A99A" stroke-width="2" stroke-linecap="round" opacity=".4" style="animation: steam 2.8s ease-in-out infinite 1s"/>
            <!-- Cup -->
            <rect x="25" y="42" width="60" height="50" rx="10" fill="#D4B896"/>
            <rect x="30" y="42" width="50" height="8" rx="4" fill="#C47F5E"/>
            <!-- Handle -->
            <path d="M85 55 Q100 55 100 70 Q100 85 85 85" stroke="#D4B896" stroke-width="5" fill="none" stroke-linecap="round"/>
            <!-- Saucer -->
            <ellipse cx="55" cy="96" rx="40" ry="6" fill="#E2CDB1"/>
        </svg>

        <div class="tagline">
            <span><?= esc($nama_warkop) ?></span>
            An exquisite coffee experience, crafted for the discerning.
        </div>
    </div>

    <!-- RIGHT: Login Form -->
    <div class="login-form-side">

        <div style="display:flex; justify-content:center; margin-bottom:16px;">
            <img src="<?= base_url($logo) ?>"
                 style="width:180px; height:110px; object-fit:contain; border-radius:8px; border:2px solid var(--nude); background:#fff;"
                 alt="Logo Lower Manhattan Coffee Shop">
        </div>

        <h2>Selamat Datang</h2>
        <p class="subtitle">Masuk ke dashboard <?= esc($nama_warkop) ?></p>

        <?php if (!empty($msg)): ?>
            <div class="error-msg shake">
                <?= esc($msg) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('/login/process') ?>" id="loginForm">

            <div class="float-label">
                <input type="text" name="username" id="username" placeholder=" " required autocomplete="username">
                <label for="username">Username</label>
            </div>

            <div class="float-label">
                <input type="password" name="password" id="password" placeholder=" " required autocomplete="current-password">
                <label for="password">Password</label>
                <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Toggle password visibility">
                    <span id="eyeIcon">👁</span>
                </button>
            </div>

            <button class="btn btn-block btn-lg" type="submit" id="loginBtn" style="margin-top:8px;">
                Masuk
            </button>

        </form>

        <p style="text-align:center; margin-top:20px; color:var(--taupe); font-size:var(--fs-xs);">
            Login menggunakan akun kasir / admin
        </p>

    </div>

</div>

<footer>
    © <?= date('Y') ?> <?= esc($nama_warkop) ?>
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
<script>
function togglePassword() {
    var inp = document.getElementById('password');
    var icon = document.getElementById('eyeIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.textContent = '🙈';
    } else {
        inp.type = 'password';
        icon.textContent = '👁';
    }
}

// Loading spinner on submit
document.getElementById('loginForm').addEventListener('submit', function() {
    var btn = document.getElementById('loginBtn');
    btn.classList.add('btn-loading');
    btn.textContent = 'Memproses...';
});
</script>

</body>
</html>