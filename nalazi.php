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