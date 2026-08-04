<?php
$file = __DIR__ . '/../app/Http/Controllers/NewMicrotikController.php';
$lines = file($file);
$depth = 0;
for ($i=0; $i<count($lines); $i++) {
    $line = rtrim($lines[$i], "\r\n");
    $open = substr_count($line, '{');
    $close = substr_count($line, '}');
    $depth += $open - $close;
    $ln = $i + 1;
    if ($ln >= 600 && $ln <= 748) {
        printf("%4d [%2d] %s\n", $ln, $depth, $line);
    }
}
