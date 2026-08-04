<?php
$file = 'c:\\inetpub\\wwwroot\\radi-micro\\app\\Http\\Controllers\\NewMicrotikController.php';
$lines = file($file);
$depth = 0;
foreach ($lines as $i => $l) {
    $open = substr_count($l, '{');
    $close = substr_count($l, '}');
    $depth += $open - $close;
    $lineno = $i + 1;
    if ($lineno === 745) {
        echo "Depth at line 745: $depth\n";
    }
    if ($depth < 0) {
        echo "Negative depth at line $lineno\n";
        break;
    }
}
echo "Final depth: $depth\n";
