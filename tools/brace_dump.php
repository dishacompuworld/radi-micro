<?php
$file = 'c:\\inetpub\\wwwroot\\radi-micro\\app\\Http\\Controllers\\NewMicrotikController.php';
$lines = file($file);
$depth = 0;
$start = 1380; $end = 1440;
for ($i = $start-1; $i < $end && $i < count($lines); $i++) {
    $l = rtrim($lines[$i], "\r\n");
    $open = substr_count($l, '{');
    $close = substr_count($l, '}');
    $depth += $open - $close;
    $lineno = $i + 1;
    echo sprintf("%4d [%3d] %s\n", $lineno, $depth, $l);
}
