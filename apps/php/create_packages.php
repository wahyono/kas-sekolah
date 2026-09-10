<?php
$rootDir = __DIR__;
$targetUpdateZip = $rootDir . '/feature-parity-update.zip';
$targetCpanelZip = $rootDir . '/kas-sekolah-cpanel.zip';

echo "Building feature-parity-update.zip...\n";
if (file_exists($targetUpdateZip)) {
    unlink($targetUpdateZip);
}

$zip = new ZipArchive();
if ($zip->open($targetUpdateZip, ZipArchive::CREATE) !== true) {
    die("Cannot create $targetUpdateZip\n");
}

$filesToInclude = [
    '.env.cpanel',
    '.htaccess',
    'app',
    'config',
    'database',
    'public',
    'resources',
    'routes',
    'vendor/setasign/fpdf'
];

foreach ($filesToInclude as $item) {
    $fullPath = $rootDir . '/' . $item;
    if (is_file($fullPath)) {
        $zip->addFile($fullPath, $item);
    } elseif (is_dir($fullPath)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootDir) + 1);
            $relativePath = str_replace('\\', '/', $relativePath);
            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
}
$zip->close();
echo "feature-parity-update.zip created: " . round(filesize($targetUpdateZip) / 1024, 2) . " KB\n";

echo "\nBuilding kas-sekolah-cpanel.zip (full release)...\n";
if (file_exists($targetCpanelZip)) {
    unlink($targetCpanelZip);
}

$zipFull = new ZipArchive();
if ($zipFull->open($targetCpanelZip, ZipArchive::CREATE) !== true) {
    die("Cannot create $targetCpanelZip\n");
}

$excludePatterns = [
    '/\.git/',
    '/storage\/logs\/.*\.log$/',
    '/storage\/framework\/cache\/data\/.+/',
    '/storage\/framework\/sessions\/.+/',
    '/storage\/framework\/views\/.+/',
    '/\.zip$/',
    '/create_packages\.php$/'
];

$iteratorFull = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iteratorFull as $file) {
    $filePath = $file->getRealPath();
    $relativePath = substr($filePath, strlen($rootDir) + 1);
    $relativePath = str_replace('\\', '/', $relativePath);

    // Check excludes
    $skip = false;
    foreach ($excludePatterns as $pattern) {
        if (preg_match($pattern, $relativePath)) {
            $skip = true;
            break;
        }
    }
    if ($skip) continue;

    if ($file->isDir()) {
        $zipFull->addEmptyDir($relativePath);
    } else {
        $zipFull->addFile($filePath, $relativePath);
    }
}
$zipFull->close();
echo "kas-sekolah-cpanel.zip created: " . round(filesize($targetCpanelZip) / (1024 * 1024), 2) . " MB\n";
