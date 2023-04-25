<?php
spl_autoload_register(function ($class) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
    $file = preg_replace('#^\\\?GitModx#', 'src', $file);
    $file = dirname(__FILE__).DIRECTORY_SEPARATOR.$file;

    if (file_exists($file)) {
        require $file;
        return true;
    }
    return false;
});
