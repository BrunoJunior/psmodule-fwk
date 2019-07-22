<?php
/**
 * 2019 BJ
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    BJ <perso@bdesprez.com>
 *  @copyright 2019 BJ
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * @param $dir
 * @return array
 */
function scan_dir($dir)
{
    $files = array();
    foreach (scandir($dir) as $file) {
        if (in_array($file, ['.', '..'])) {
            continue;
        }
        $ext = strtolower(substr($file, -4));
        $cheminAbs = $dir . '/' . $file;
        if (is_dir($file)) {
            $files[$file] = scan_dir($cheminAbs);
            continue;
        }
        if (strlen($file) < 5 || !in_array($ext, ['.txt', '.log'])) {
            continue;
        }
        $files[] = ['name' => $file, 'time' => filemtime($cheminAbs)];
    }
    uasort($files, function ($f1, $f2) {
        if (!array_key_exists('time', $f1) && !array_key_exists('time', $f2)) {
            return ($f1 <= $f2) ? -1 : 1;
        }
        if (!array_key_exists('time', $f1) || $f1['time'] === false) {
            return -1;
        }
        if (!array_key_exists('time', $f2) || $f2['time'] === false) {
            return 1;
        }
        return $f2['time'] - $f1['time'];
    });
    return $files;
}

/**
 * print files in list
 * @param array $files
 * @param string $rel
 */
function print_tree_view($files, $rel = '') {
    $rel = empty($rel) ? '' : "$rel/";
    foreach ($files as $dirName => $file) {
        $isFile = array_key_exists('name', $file);
        $lib = $isFile ? $file['name'] : '<span class="caret">'.$dirName.'</span>';
        if ($isFile) {
            $lib = "<a href=\"$rel{$file['name']}\">$lib</a>";
        }
        echo "<li>$lib";
        if (!$isFile) {
            echo "<ul class='nested'>";
            print_tree_view($file, "{$rel}$dirName");
            echo "</ul>";
        }
        echo "</li>";
    }
}

$files = scan_dir(__DIR__);
if (empty($files)) {
    die('No files');
}


echo <<<EOT
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Logs</title>
            <link rel="stylesheet" href="logs.css" />
    </head>
    <body>
        <ul id="myUL">
EOT;

print_tree_view($files);
echo <<<EOT
        </ul>
        <script type="text/javascript" src="logs.js"></script>
    </body>
</html>
EOT;
