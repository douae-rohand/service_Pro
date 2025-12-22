<?php
$path = __DIR__ . '/bootstrap/cache';
echo "Checking path: $path\n";
echo "Exists: " . (file_exists($path) ? 'Yes' : 'No') . "\n";
echo "Is Dir: " . (is_dir($path) ? 'Yes' : 'No') . "\n";
echo "Writable: " . (is_writable($path) ? 'Yes' : 'No') . "\n";
