<?php

$root = dirname(__DIR__);
$directories = ['app', 'routes', 'config', 'database', 'tests'];
$checked = 0;
$errors = [];

foreach ($directories as $directory) {
    $path = $root.DIRECTORY_SEPARATOR.$directory;
    if (! is_dir($path)) {
        continue;
    }

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    foreach ($files as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $checked++;
        try {
            token_get_all(file_get_contents($file->getPathname()), TOKEN_PARSE);
        } catch (ParseError $error) {
            $errors[] = $file->getPathname().':'.$error->getLine().' '.$error->getMessage();
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors).PHP_EOL);
    exit(1);
}

echo "Pemeriksaan sintaks statis lulus untuk {$checked} file PHP.".PHP_EOL;
