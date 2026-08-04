<?php
$file = __DIR__ . '/../app/Http/Controllers/NewMicrotikController.php';
$lines = file($file);
$methodLine = null;
foreach ($lines as $i => $l) {
    if (strpos($l, 'public function viewLog(') !== false) { $methodLine = $i+1; break; }
}
if (!$methodLine) { echo "method not found\n"; exit; }
$depth = 0;
for ($i=0;$i<count($lines);$i++){
    $open = substr_count($lines[$i], '{');
    $close = substr_count($lines[$i], '}');
    $depth += $open - $close;
    $ln = $i+1;
    if ($ln > $methodLine && $depth == 1) {
        // function body open; now find where it returns to depth- before function
    }
    if ($ln > $methodLine && $depth == 0) {
        echo "viewLog ends at line: $ln\n"; break;
    }
}
