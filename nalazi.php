<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

$user = require_login();

$uploadMessage = '';
$uploadMessageType = '';
$reportsDir = __DIR__ . '/nalazi';
$reportsUrl = 'nalazi';

if (!is_dir($reportsDir)) {
    mkdir($reportsDir, 0777, true);
}

function safe_report_prefix(string $username): string
{
    $prefix = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($username));
    return trim($prefix ?: 'user', '_');
}

function format_file_size(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    }

    return number_format($bytes / 1024, 1) . ' KB';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_pdf') {
    $file = $_FILES['nalaz_pdf'] ?? null;
    $maxSize = 10 * 1024 * 1024;

    if ($file === null || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        $uploadMessage = 'Odaberite PDF nalaz za upload.';
        $uploadMessageType = 'error';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadMessage = 'Upload nije uspio. Pokusajte ponovno.';
        $uploadMessageType = 'error';
    } elseif ((int) $file['size'] > $maxSize) {
        $uploadMessage = 'PDF moze imati najvise 10 MB.';
        $uploadMessageType = 'error';
    } else {
        $tmpPath = (string) $file['tmp_name'];
        $originalName = (string) $file['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $handle = fopen($tmpPath, 'rb');
        $signature = $handle !== false ? fread($handle, 4) : '';

        if ($handle !== false) {
            fclose($handle);
        }

        if ($extension !== 'pdf' || $signature !== '%PDF') {
            $uploadMessage = 'Dozvoljeni su samo PDF nalazi.';
            $uploadMessageType = 'error';
        } else {
            $prefix = safe_report_prefix($user['username']);
            $targetName = $prefix . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.pdf';
            $targetPath = $reportsDir . '/' . $targetName;

            if (move_uploaded_file($tmpPath, $targetPath)) {
                $uploadMessage = 'PDF nalaz je uspjesno spremljen.';
                $uploadMessageType = 'success';
            } else {
                $uploadMessage = 'Nalaz nije moguce spremiti u folder nalazi.';
                $uploadMessageType = 'error';
            }
        }
    }
}

$reportPrefix = safe_report_prefix($user['username']);
$savedReports = glob($reportsDir . '/' . $reportPrefix . '-*.pdf') ?: [];
usort($savedReports, static fn(string $a, string $b): int => filemtime($b) <=> filemtime($a));

render_header('Nalazi', 'nalazi');
?>
<section class="panel">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Unos nalaza</p>
            <h2>Uploadaj nalaz</h2>
        </div>
        <span class="badge neutral">PDF upload</span>
    </div>

    <?php if ($uploadMessage !== ''): ?>
        <div class="alert <?= htmlspecialchars($uploadMessageType, ENT_QUOTES, 'UTF-8'); ?>">
            <?= htmlspecialchars($uploadMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form class="upload-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_pdf">
        <label class="upload-card" for="nalaz_pdf" data-upload-card>
            <input id="nalaz_pdf" type="file" name="nalaz_pdf" accept="application/pdf,.pdf" required data-file-input>
            <span class="upload-icon">&uarr;</span>
            <strong data-file-label>Povuci ili odaberi PDF nalaz ovdje</strong>
            <p>PDF se sprema u folder <strong>nalazi</strong> i ostaje povezan s prijavljenim korisnikom.</p>
            <span class="button secondary">Odaberi datoteku</span>
        </label>
        <button class="button primary" type="submit">Spremi PDF nalaz</button>
    </form>
</section>

<section class="panel analysis-result" data-analysis-result>
    <h2>AI analizira tvoj nalaz</h2>
    <p>Unesite vrijednost i pokrenite analizu za usporedbu s referentnim vrijednostima.</p>
</section>

<section class="panel">
    <h2>Spremljeni nalazi</h2>
    <?php if ($savedReports === []): ?>
        <div class="empty-state">
            <p>Jos nema spremljenih PDF nalaza za ovaj profil.</p>
        </div>
    <?php else: ?>
        <div class="reports-list">
            <?php foreach ($savedReports as $reportPath): ?>
                <?php
                $fileName = basename($reportPath);
                $uploadedAt = date('d.m.Y. H:i', filemtime($reportPath));
                ?>
                <article class="report-item">
                    <div>
                        <strong><?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8'); ?></strong>
                        <p><?= htmlspecialchars($uploadedAt, ENT_QUOTES, 'UTF-8'); ?> &middot; <?= htmlspecialchars(format_file_size((int) filesize($reportPath)), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <a class="button secondary" href="<?= htmlspecialchars($reportsUrl . '/' . rawurlencode($fileName), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Otvori PDF</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
