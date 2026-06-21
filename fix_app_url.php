<?php
$dir = new RecursiveDirectoryIterator('c:/xampp/htdocs/attendance_system/public/teacher');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.php$/', RegexIterator::GET_MATCH);

foreach($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    $pattern = "/define\('APP_ROOT'.*?\r?\n.*define\('APP_URL'.*?\r?\n.*define\('APP_ENV'.*?\r?\n\r?\nrequire_once APP_ROOT \. '\/config\/config\.php';/s";
    $replacement = "require_once dirname(__DIR__, 3) . '/config/config.php';";
    $newContent = preg_replace($pattern, $replacement, $content);
    if ($content !== $newContent) {
        file_put_contents($path, $newContent);
        echo "Fixed $path\n";
    }
}
echo "Done.\n";
