<?php

$options = getopt('d:t:m:');
if (!array_key_exists('t', $options)) {
    $options['t'] = '[A Valider]';
} else {
    $options['t'] = '[' . $options['t'] . ']';
}

if(!array_key_exists('m', $options)) {
    $options['m'] = 'psmodulefwk';
}

$modulesPath = dirname(__DIR__);
$modulePath = $modulesPath . '/' . $options['m'];

if (!file_exists($modulePath)) {
    die("Le module {$options['m']} est introuvable !");
}

$moduleFile = "{$modulePath}/{$options['m']}.php";
$contentModule = file_get_contents($moduleFile);
$patternVersion = '/\$this->version = \'(.+)\';/';
$patternInternalVersion = '/(public \$internalVersion = \')(.+)(\';)/';
$internalVersion = date('YmdHi');

$matches = [];
preg_match_all($patternVersion, $contentModule, $matches);
$version = array_key_exists(1, $matches) ? $matches[1][0] : 'x.x.x.x';

// On met à jour la version interne
file_put_contents($moduleFile, preg_replace($patternInternalVersion, '${1}' . $internalVersion . '${3}', $contentModule));

echo "Packaging {$options['m']} module v" . $version . PHP_EOL;
$zipname = "{$options['m']}-$version";
$zipPath = "{$modulesPath}/{$zipname}.zip";

// Initialize archive object
$zip = new ZipArchive();
$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

echo 'Zip initialized' . PHP_EOL;

// Will exclude everything under these directories
$excludeDir = ['.svn', 'skeleton'];
$start = '.*[/\\\]'.$options['m'];
$excludeFiles = [
    $start.'[/\\\]init_module\\.php',
    $start.'[/\\\](generate_)?autocomplete\\.php',
    $start.'[/\\\]config_fr\\.xml',
    $start.'[/\\\]package\\.php',
    $start.'[/\\\]logs[/\\\].*\\.log',
    $start.'[/\\\]logs[/\\\].*\\.txt'
];

/**
 * @param SplFileInfo $file
 * @param mixed $key
 * @param RecursiveCallbackFilterIterator $iterator
 * @return bool True if you need to recurse or if the item is acceptable
 */
$filter = function ($file, $key, $iterator) use ($excludeDir, $excludeFiles) {
    if ($iterator->hasChildren() && !in_array($file->getFilename(), $excludeDir)) {
        return TRUE;
    }
    if (!$file->isFile()) {
        echo $key . ' skipped' . PHP_EOL;
        return FALSE;
    }
    foreach ($excludeFiles as $pattern) {
        $pattern = '#' . $pattern . '#';
        if (preg_match($pattern, $key)) {
            echo $key . ' skipped' . PHP_EOL;
            return FALSE;
        }
    }
    return TRUE;
};

$innerIterator = new RecursiveDirectoryIterator($modulePath, RecursiveDirectoryIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator($innerIterator, $filter));

echo 'Adding files in zip' . PHP_EOL;

foreach ($iterator as $name => $file) {
    // Skip directories (they would be added automatically)
    if (!$file->isDir()) {
        // Get real and relative path for current file
        $filePath = str_replace('\\', '/', $file->getRealPath());
        $relativePath = $options['m'] . '/' . substr($filePath, strlen($modulePath) + 1);
        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
    }
}

// Zip archive will be created only after closing object
$zip->close();

echo 'Packaging done' . PHP_EOL;

if (array_key_exists('d', $options)) {
    echo 'Sending to ' . $options['d'] . PHP_EOL;
    if (copy($zipPath, $options['d'] . $zipname . $options['t'] . '.zip')) {
        echo 'Sent' . PHP_EOL;
    } else {
        echo 'Sending failed' . PHP_EOL;
    }
}
