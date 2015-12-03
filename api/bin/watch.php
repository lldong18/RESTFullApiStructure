#!/usr/bin/env php
<?php
define('FILENAME_REGEX', '/(.php|.yml|.twig)$/');
$watchDir = __DIR__ . '/..';
$ignoreDirs = array(
  $watchDir . '.git',
  $watchDir . '/bin',
  $watchDir . '/vendor',
  $watchDir . '/example/build',
);
// dir is not recursive, atm
$dir = array(
  $watchDir,
);
$cmd = array(
  'clear',
  'make',
);

if (!is_array($dir)) {
  $dir = array($dir);
}

if (!is_array($cmd)) {
  $cmd = array($cmd);
}

$file_cache = get_mtimes($dir, $ignoreDirs);

echo "[" . get_date() . "] Start watching\n";

while(1) {
  //echo "Start: " . time() . "\n";

  $files = get_mtimes($dir, $ignoreDirs);
  //var_dump($files);
  $cached_files = array_keys($file_cache);
  $new_files = array_keys($files);

  $deleted_files = array_diff($cached_files, $new_files);
  foreach ($deleted_files as $file) {
    unset($file_cache[$file]);
  }

  $added_files = array_diff($new_files, $cached_files);
  foreach ($added_files as $file) {
    $file_cache[$file] = $files[$file];
  }

  $updated_files = array();
  foreach (array_intersect($new_files, $cached_files) as $file) {
    if ($file_cache[$file] < $files[$file]) {
      $file_cache[$file] = $files[$file];
      $updated_files[] = $file;
    }
  }

  $d = count($deleted_files);
  $a = count($added_files);
  $u = count($updated_files);
  if ($d || $a || $u) {
    echo "[" . get_date() . "] Changes detected (d=$d) (a=$a) (u=$u)\n";
    /*
    if ($d) {
      echo "d=$d\n";
      foreach ($deleted_files as $file) {
        echo "  - $file\n";
      }
    }
    if ($a) {
      echo "a=$a\n";
      foreach ($added_files as $file) {
        echo "  - $file\n";
      }
    }
    if ($u) {
      echo "u=$u\n";
      foreach ($updated_files as $file) {
        echo "  - $file\n";
      }
    }
    */

    $start = microtime(true);
    foreach ($cmd as $command) {
      echo "\033[33m";
      echo PHP_EOL . str_repeat('=', 80) . PHP_EOL;
      echo "[" . get_date() . "] Executing: $command\n";
      echo str_repeat('=', 80) . PHP_EOL;
      echo "\033[0m";
      //exec($command);
      system($command);
      echo PHP_EOL;
    }
    $end = microtime(true);
    echo "[" . get_date() . "] Done. Took " . round($end-$start, 2) . " seconds\n";
  }

  sleep(1);
}

function get_mtimes($dirs, $ignoreDirs) {
  $mtimes = array();

  if (!is_array($dirs)) {
    $dirs = array($dirs);
  }

  clearstatcache();
  foreach ($dirs as $dir) {
    if (in_array($dir, $ignoreDirs)) continue;

    $dh = opendir($dir);
    if (!$dh) die('Unable to read dir: ' . $dir);

    while (($entry = readdir($dh)) !== false) {
      if (in_array($entry, array('.', '..'))) continue;

      $fullPath = "$dir/$entry";

      if (is_dir($fullPath)) {
        $mtimes = array_merge($mtimes, get_mtimes($fullPath, $ignoreDirs));
      } else if (preg_match(FILENAME_REGEX, $entry)) {
        $mtimes[$fullPath] = filemtime($fullPath);
      }
    }
    closedir($dh);
  }

  return $mtimes;
}

function get_date() {
  return date('Y-m-d h:i:s a');
}


