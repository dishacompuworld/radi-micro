<?php
$file = 'c:\\inetpub\\wwwroot\\radi-micro\\app\\Http\\Controllers\\NewMicrotikController.php';
$lines = file($file);
$depth = 0;
foreach ($lines as $i => $l) {
    $lineno = $i + 1;
    $open = substr_count($l, '{');
    $close = substr_count($l, '}');
    $depth += $open - $close;
    echo sprintf("%4d [%3d] %s", $lineno, $depth, rtrim($l, "\r\n"));
}
echo "\nFinal depth: $depth\n";
