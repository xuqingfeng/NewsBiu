<?php
/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/26
 */

error_reporting(E_ALL);

date_default_timezone_set('Asia/Shanghai');

define('ROOT_PATH', __DIR__ . '/../');

set_include_path(ROOT_PATH . PATH_SEPARATOR . get_include_path());

$config = new \Phalcon\Config\Adapter\Ini(ROOT_PATH . 'app/config/config.dev.ini');

$loader = new \Phalcon\Loader();
$loader->registerDirs([
    __DIR__,
    ROOT_PATH . $config->application->modelsDir
])->register();

$di = new \Phalcon\DI\FactoryDefault();
\Phalcon\DI::reset();

/**
 * services
 *
 * services split out
 */
$di->set('config', $config, true);
$di->set('router', function () {

    $router = new \Phalcon\Mvc\Router();
    $router->add('/{tab:[a-zA-Z]+}', [
        'controller' => 'index',
        'action'     => 'index',
        'tab'        => 1
    ]);
    $router->add('/n/{date:[0-9]{8}}/{time:[a-zA-Z0-9_]+}', [
        'controller' => 'news',
        'action'     => 'n',
        'date'       => 1,
        'time'       => 2
    ]);
    $router->add('/q/{date:[0-9]{8}}/{time:[a-zA-Z0-9_]+}', [
        'controller' => 'question',
        'action'     => 'q',
        'date'       => 1,
        'time'       => 2
    ]);

    return $router;
}, true);
$di->set('eventsManager', function () use ($di) {

    return new \Phalcon\Events\Manager();
}, true);
$di->set('dispatcher', function () use ($di) {

//        $eventsManager = new \Phalcon\Events\Manager();
    $eventsManager = $di->getShared('eventsManager');
    $eventsManager->attach('dispatch:beforeDispatch', new Security);
    $dispatcher = new \Phalcon\Mvc\Dispatcher();
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
}, true);
$di->set('view', function () use ($config) {

    $view = new \Phalcon\Mvc\View();
    $view->setViewsDir(ROOT_PATH . $config->application->viewsDir);

    return $view;
}, true);
$di->set('mongo', function () use ($config) {

    // '://' fuck me
    $mongo = new MongoClient('mongodb://' . $config->database->username . ':' . $config->database->password . '@' . $config->database->host . ':' . $config->database->port);

    // fucked
    return $mongo->selectDB($config->database->name);
//        return $mongo;
}, true);
// mongo related
$di->set('collectionManager', function () {

    return new \Phalcon\Mvc\Collection\Manager();
});
$di->set('cookies', function () {

    $cookies = new \Phalcon\Http\Response\Cookies();
    $cookies->useEncryption(false);

    return $cookies;
});
$di->set('crypt', function () use ($config) {

    $crypt = new \Phalcon\Crypt();
//        $crypt->setKey($config->application->encryptKey);
    $crypt->setKey('NewsBiuIsAwesome');

    return $crypt;
});
$di->set('session', function () {

    $session = new \Phalcon\Session\Adapter\Files();
    $session->start();

    return $session;
}, true);
$di->set('flash', function () {

    return new \Phalcon\Flash\Session([]);
}, true);
require_once ROOT_PATH . 'app/vendor/autoload.php';
$di->set('parsedown', function () {

    return Parsedown::instance();
}, true);


\Phalcon\DI::setDefault($di);