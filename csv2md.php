<?php
const NO_SUBTOPIC = '0__no_subtopic__';

if (!isset($argv[2])) {
  echo "Usage: php csv2md.php input_file output_file.csv" . PHP_EOL;
  exit(1);
}

$csv = $argv[1];
if (!file_exists($csv)) {
  echo "File not found: $csv" . PHP_EOL;
  exit(1);
}

$outfile = $argv[2];
if ($csv == $outfile) {
  echo "Cannot write on input file!" . PHP_EOL;
  exit(1);
}

$countents = [];
$content = '';
$topic = '';
$prevtopic = '';
$subtopic = '';
$prevsubtopic = '';
$resourcesNumber = 0;
if (($handle = fopen($csv, "r")) !== false) {
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        list($topic, $subtopic, $name, $url, $description) = $data;

        if (!isset($contents[$topic])) {
          $contents[$topic] = [];
        }

        if (empty($subtopic)) {
          $subtopic = NO_SUBTOPIC;
        }

        if (!isset($contents[$topic][$subtopic])) {
          $contents[$topic][$subtopic] = [];
        }

        $contents[$topic][$subtopic][$name] = [
            'url' => $url,
            'description' => $description,
        ];
      $resourcesNumber ++;
    }
    fclose($handle);
}

$toc = '';
foreach ($contents as $topic => $subtopics)
{
  $toc .= sprintf("* [%s](#%s)", $topic, str_replace(' ', '-', preg_replace('/[^a-z ]/', '', strtolower($topic)))) . PHP_EOL;
  $content .= sprintf(PHP_EOL . "## %s" . PHP_EOL, $topic);
  ksort($subtopics);
  foreach ($subtopics as $subtopic => $subtopicdata) {
    $content .= PHP_EOL;
    if (NO_SUBTOPIC != $subtopic) {
      $content .= sprintf("### %s" . PHP_EOL . PHP_EOL, $subtopic);
    }
    uksort($subtopicdata, 'strnatcasecmp');
    foreach ($subtopicdata as $name => $data) {
      $content .= sprintf("* [%s](%s)%s%s" . PHP_EOL, $name, $data['url'], empty($data['description']) ? '' : ' - ', $data['description']);
    }
  }
}

$introduction = <<<OUT
# Magento 2 Resources [![contributions welcome](https://img.shields.io/badge/contributions-welcome-brightgreen.svg?style=flat)](CONTRIBUTING.md) [![$resourcesNumber resources](https://img.shields.io/badge/resources-$resourcesNumber-orange.svg?style=flat)](#table-of-contents) [![Links Health Status](https://github.com/aleron75/mageres/workflows/Check%20Links%20Health/badge.svg)](https://github.com/aleron75/mageres/actions?query=workflow%3A%22Check+Links+Health%22)

<p align="center">
<img src="https://raw.githubusercontent.com/aleron75/mageres/master/media/mageres.png" alt="mageres logo"/>
</p>

A curated list of **useful Magento 2 resources**.
Resources are listed **alphabetically** within each category.

This file is automatically generated from the [resources.csv](resources.csv) file by an automatic GitHub action.

If you want to contribute, just update the `resources.csv` and submit a PR.

Note: we also have archived [resources list related to the glorious Magento 1](README-M1.md).

## Stay up to date!

If you want to **stay up to date with changes**, you can [subscribe to the monthly digest](https://mailchi.mp/6a498018d9ef/mageres).

OUT;

$toc = PHP_EOL . '## Table of Contents' . PHP_EOL . PHP_EOL . $toc;

$out = $introduction . $toc . $content;

$fp = fopen($outfile, 'w');
fwrite($fp, $out);
fclose($fp);
#echo $out;
#print_r($contents);
