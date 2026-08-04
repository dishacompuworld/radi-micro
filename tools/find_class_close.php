<?php
$file = __DIR__ . '/../app/Http/Controllers/NewMicrotikController.php';
$lines = file($file);
$classLine = null;
foreach ($lines as $i => $l) {
    if (strpos($l, 'class NewMicrotikController') !== false) { $classLine = $i+1; break; }
}
echo "class at: ".($classLine ?: 'not found')."\n";
$depth = 0;
for ($i=0;$i<count($lines);$i++){
    $open = substr_count($lines[$i], '{');
    $close = substr_count($lines[$i], '}');
    $depth += $open - $close;
    $ln = $i+1;
    if ($ln > $classLine && $depth == 0){
        echo "class body closed at line: $ln\n";
        break;
    }
}
echo "final depth: $depth\n";
