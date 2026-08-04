<?php
$file = __DIR__ . '/../app/Http/Controllers/NewMicrotikController.php';
$lines = file($file);
for ($i = 0; $i < count($lines); $i++) {
    $ln = $i + 1;
    if ($ln >= 730 && $ln <= 747) {
        echo sprintf("%4d: %s", $ln, $lines[$i]);
    }
}
