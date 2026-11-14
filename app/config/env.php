<?php

/**
 * Режим застосунку: береться зі змінної оточення YII_ENV (dev | test | prod).
 *
 * Якщо змінну не задано — це prod: налагодження вимкнене, Gii та debug-панель
 * не підключаються, помилки показуються як звичайна сторінка "Щось пішло не так".
 *
 * Локально (docker) режим dev вмикається через файл .env у корені проєкту:
 *     YII_ENV=dev
 * На хостингу без Docker можна задати в .htaccess:  SetEnv YII_ENV dev
 */

$env = getenv('YII_ENV') ?: ($_SERVER['YII_ENV'] ?? $_SERVER['REDIRECT_YII_ENV'] ?? 'prod');

if (!in_array($env, ['dev', 'test', 'prod'], true)) {
    $env = 'prod';
}

defined('YII_ENV') or define('YII_ENV', $env);
defined('YII_DEBUG') or define('YII_DEBUG', $env === 'dev');
