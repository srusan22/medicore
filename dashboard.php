<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

$user = require_login();
render_header('Dashboard', 'dashboard');
?>
<section class="welcome-band">
    <div>
        <p class="eyebrow">Dobro došli</p>
        <h2>Razumij svoje nalaze za 60 sekundi</h2>
        <p><?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?>, učitaj nalaz, usporedi vrijednosti i spremi objašnjenja na jedno mjesto.</p>
    </div>
    <a class="button secondary" href="nalazi.php">Dodaj nalaz</a>
</section>
