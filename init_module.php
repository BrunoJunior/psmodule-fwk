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

$options = getopt('m:n:d:s:');

if (!array_key_exists('s', $options)) {
    die("L'option -s (espace de nom) est obligatoire !");
}

if (!array_key_exists('m', $options)) {
    die("L'option -m (nom du module) est obligatoire !");
}

if (!array_key_exists('d', $options)) {
    $options['d'] = $options['m'];
}
if(!array_key_exists('n', $options)) {
    $options['n'] = $options['m'];
}

$moduleName = mb_strtolower($options['m']);
$modulesPath = dirname(__DIR__);
$modulePath = $modulesPath . '/' . $moduleName;

if (file_exists($modulePath)) {
    die('Le module "'.$moduleName.'" existe déjà !');
}

function replace($content, $arrReplacement) {
    foreach ($arrReplacement as $search => $replace) {
        $content = str_replace("[{$search}]", $replace, $content);
    }
    return $content;
}

function recursiveCopy($src, $dst, $remplacementFunction = null) {
    echo "Copy $src > $dst \n";
    $dir = opendir($src);
    @mkdir($dst);
    while(( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recursiveCopy($src .'/'. $file, $dst .'/'. $file);
            }
            else {
                $newFile = $file;
                if (is_callable($remplacementFunction)) {
                    $newFile = $remplacementFunction($file);
                }
                $dstFile = $dst .'/'. $newFile;
                copy($src .'/'. $file, $dstFile);
                if (is_callable($remplacementFunction)) {
                    file_put_contents($dstFile, $remplacementFunction(file_get_contents($dstFile)));
                }
            }
        }
    }
    closedir($dir);
}

$skeletonPath = __DIR__ . '/skeleton';
recursiveCopy($skeletonPath, $modulePath, function ($content) use ($options, $moduleName) {
    return replace($content, [
        'module' => $moduleName,
        'Module' => $options['m'],
        'name' => $options['n'],
        'description' => $options['d'],
        'namespace' => $options['s']
    ]);
});

echo "Module \"$moduleName\" initialisé avec succès !";
