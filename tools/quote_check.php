<?php
$file = __DIR__ . '/../app/Http/Controllers/NewMicrotikController.php';
$lines = file($file);
$single = 0; $double = 0;
for ($i=0;$i<count($lines);$i++){
    $line = $lines[$i];
    $single += substr_count($line, "'");
    $double += substr_count($line, '"');
    $ln = $i+1;
    if ($ln >= 1 && $ln <= 800) {
        echo sprintf("%4d s:%2d d:%2d %s", $ln, $single%2, $double%2, rtrim($line,"\r\n"));
    }
}
