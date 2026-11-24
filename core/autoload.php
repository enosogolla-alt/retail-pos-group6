<?php

declare(strict_types=1);

/**
 * Class loader for application classes. The application runs without Composer.
 *
 * Owner: Kyle Tarus (M2)
 * Tasks: 2.3
 */

spl_autoload_register(static function (string $class): void {
    foreach (['core', 'controllers', 'services', 'models'] as $directory) {
        $file = BASE_PATH . '/' . $directory . '/' . $class . '.php';

        if (is_file($file)) {
            require $file;
            return;
        }
    }
});
