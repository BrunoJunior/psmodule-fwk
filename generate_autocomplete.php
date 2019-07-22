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

function getDirContents($dir, &$results = array()){
    $files = scandir($dir);

    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if ($value === "." || $value === ".." || $value === 'index.php' || (!is_dir($path) && substr($value, -4) !== '.php')) {
            continue;
        }
        if (is_dir($path)) {
            getDirContents($path, $results);
        } else {
            $results[$path] = substr($value, 0, -4);
        }
    }

    return $results;
}

$options = getopt('m:');
if(!array_key_exists('m', $options)) {
    $options['m'] = 'psmodulefwk';
}
$modulesPath = dirname(__DIR__);
$modulePath = $modulesPath . '/' . $options['m'];

$fileHandler = fopen($modulePath . "/autocomplete.php", "w+");
fwrite($fileHandler, "<?php\n\n");
$array = getDirContents($modulesPath . '/../classes');
getDirContents($modulesPath . '/../controllers', $array);

$overrides = getDirContents($modulePath . '/override');

foreach ($array as $path => $class) {
    if (in_array($class, $overrides)) {
        continue;
    }
    $abstract = '';
    if (preg_match_all('/abstract class /', file_get_contents($path))) {
        $abstract = 'abstract ';
    }
    fwrite($fileHandler, $abstract . "class $class extends {$class}Core {}\n");
}
fclose($fileHandler);
