<?php
$file = __DIR__ . '/../app/Http/Controllers/NewMicrotikController.php';
$lines = file($file);
$depth=0;
$start=720; $end=748;
for ($i=0;$i<count($lines);$i++){
    $open = substr_count($lines[$i], '{');
    $close = substr_count($lines[$i], '}');
    $depth += $open - $close;
    $ln = $i+1;
    if ($ln >= $start && $ln <= $end) {
        echo sprintf("%4d [%3d] %s", $ln, $depth, rtrim($lines[$i], "\r\n"));
    }
}
