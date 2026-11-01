<?php

declare(strict_types=1);

$_SERVER['CI_ENVIRONMENT'] = 'testing';
define('ENVIRONMENT', 'testing');
defined('CI_DEBUG') || define('CI_DEBUG', true);

require_once dirname(__DIR__) . '/vendor/autoload.php';

$paths = new Config\Paths();
$root = realpath(dirname(__DIR__));
defined('HOMEPATH') || define('HOMEPATH', $root . DIRECTORY_SEPARATOR);
defined('CONFIGPATH') || define('CONFIGPATH', realpath($paths->appDirectory . '/Config') . DIRECTORY_SEPARATOR);
defined('PUBLICPATH') || define('PUBLICPATH', realpath($root . '/public') . DIRECTORY_SEPARATOR);
defined('APPPATH') || define('APPPATH', realpath($paths->appDirectory) . DIRECTORY_SEPARATOR);
defined('ROOTPATH') || define('ROOTPATH', $root . DIRECTORY_SEPARATOR);
defined('SYSTEMPATH') || define('SYSTEMPATH', realpath($paths->systemDirectory) . DIRECTORY_SEPARATOR);
defined('WRITEPATH') || define('WRITEPATH', realpath($paths->writableDirectory) . DIRECTORY_SEPARATOR);
defined('TESTPATH') || define('TESTPATH', realpath($root . '/tests') . DIRECTORY_SEPARATOR);
defined('SUPPORTPATH') || define('SUPPORTPATH', realpath(TESTPATH . '_support') . DIRECTORY_SEPARATOR);
defined('CIPATH') || define('CIPATH', realpath($paths->systemDirectory . '/..') . DIRECTORY_SEPARATOR);
defined('FCPATH') || define('FCPATH', PUBLICPATH);
defined('COMPOSER_PATH') || define('COMPOSER_PATH', realpath($root . '/vendor/autoload.php'));

require_once SYSTEMPATH . 'Boot.php';
$boot = new ReflectionClass(CodeIgniter\Boot::class);
foreach ([
    'loadConstants' => [],
    'checkMissingExtensions' => [],
    'loadEnvironmentBootstrap' => [$paths, false],
    'loadCommonFunctionsMock' => [],
    'loadCommonFunctions' => [],
    'loadAutoloader' => [],
    'setExceptionHandler' => [],
    'initializeKint' => [],
    'autoloadHelpers' => [],
] as $method => $arguments) {
    $reflectionMethod = $boot->getMethod($method);
    $reflectionMethod->setAccessible(true);
    $reflectionMethod->invokeArgs(null, $arguments);
}

service('routes')->loadRoutes();
