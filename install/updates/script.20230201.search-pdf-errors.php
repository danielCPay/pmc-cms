<?php

$csv = fopen('/var/www/html/errors.csv', 'w');
fputcsv($csv, ['PROBLEM', 'FILE', 'TYPE', 'PACKAGE OR TEMPLATE ID', 'RECORD ID', 'ERROR LINE NUMBER', 'ERROR LINE', 'BEGINNING LINE NUMBER', 'BEGINNING LINE']);
$libreOfficeRe = "/LibreOffice: O: [^c]/";
$pdftkRe = "/pdftk: O: , E: [a-zA-Z]+/";
$beginningRe = '/(?J)(DocumentPackages::generate:(?<packageId>[0-9]+)\/(?<recordId>[0-9]+)\/)|(DocumentTemplates::generateDocument:(?<templateId>[0-9]+)\/(?<recordId>[0-9]+))/S';
// $path = "/var/www/html/cache/logs";
$path = "/logs";
$filesProcessed = 0;
foreach (scandir($path, SCANDIR_SORT_DESCENDING) as $file) {
  if (!str_starts_with($file, 'system')) {
    continue;
  } else if (str_ends_with($file, '.gz')) {
    continue;
    $lines = file("compress.zlib://$path/$file");
  } else {
    $lines = file("$path/$file");
  }

  $cnt = 0;
  $err = 0;
  foreach ($lines as $line) {
    $cnt++;

    if (preg_match($beginningRe, $line) === 1) {
      $lines[] = trim($line);
    } else if (preg_match($libreOfficeRe, $line) === 1) {
      $err++;
      $beginningLine = '';
      $beginningLineNum = 0;

      // look through $lines
      $found = false;
      for ($i = $cnt; $i >= 0 && !$found; $i--) {
        if (preg_match($beginningRe, $lines[$i], $matches) === 1) {
          ['packageId' => $packageId, 'templateId' => $templateId, 'recordId' => $recordId] = $matches;
          $beginningLine = trim($lines[$i]);
          $beginningLineNum = $i + 1;
          $found = true;
          break;
        }
      }

      fputcsv($csv, ['LibreOffice Problem', $file, (!empty($packageId) ? "PACKAGE" : (!empty($templateId) ? "TEMPLATE" : "UNKNOWN")), $packageId ?: $templateId, $recordId, $cnt, $line, $beginningLineNum, $beginningLine]);
    } else if (preg_match($pdftkRe, $line) === 1) {
      $err++;
      $beginningLine = '';
      $beginningLineNum = 0;

      // look through $lines
      $found = false;
      for ($i = $cnt; $i >= 0 && !$found; $i--) {
        if (preg_match($beginningRe, $lines[$i], $matches) === 1) {
          ['packageId' => $packageId, 'templateId' => $templateId, 'recordId' => $recordId] = $matches;
          $beginningLine = trim($lines[$i]);
          $beginningLineNum = $i + 1;
          $found = true;
          break;
        }
      }

      fputcsv($csv, ['Concatenation Problem', $file, (!empty($packageId) ? "PACKAGE" : (!empty($templateId) ? "TEMPLATE" : "UNKNOWN")), $packageId ?: $templateId, $recordId, $cnt, $line, $beginningLineNum, $beginningLine]);
    }
  }

  unset($lines);

  echo "$file - $cnt lines, $err errors" . PHP_EOL;

  // if ($filesProcessed++ > 10) {
  //   break;
  // }
}
fclose($csv);
