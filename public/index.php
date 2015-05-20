<?php
/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/3
 */

//phpinfo();
//print_r(get_loaded_extensions());
//exit;

try {

    define('ROOT_PATH', __DIR__ . '/../');

    ini_set('xdebug.var_display_max_depth', -1);
    ini_set('xdebug.var_display_max_children', -1);
    ini_set('xdebug.var_display_max_data', -1);

//    print_r(get_loaded_extensions());
//    exit;

    define('STATE', 'dev');
    if (STATE == 'dev') {
        $config = new \Phalcon\Config\Adapter\Ini(ROOT_PATH . 'app/config/config.dev.ini');
    } else if (STATE == 'prd') {
        $config = new \Phalcon\Config\Adapter\Ini(ROOT_PATH . 'app/config/config.prd.ini');
    }

    date_default_timezone_set($config->environment->timezone);

    $loader = new \Phalcon\Loader();
    $loader->registerDirs([
        ROOT_PATH . $config->application->controllersDir,
        ROOT_PATH . $config->application->modelsDir,
        ROOT_PATH . $config->application->viewsDir,
        ROOT_PATH . $config->application->pluginsDir,
        ROOT_PATH . $config->application->vendorDir
    ])->register();

    $di = new \Phalcon\DI\FactoryDefault();
    $di->set('config', $config, true);
    $di->set('router', function () {

        $router = new \Phalcon\Mvc\Router();
        $router->add('/{tab:[a-zA-Z]+}', [
            'controller' => 'index',
            'action'     => 'index',
            'tab'        => 1
        ]);
        $router->add('/n/{date:[0-9]{8}}/{time:[0-9]{6}}', [
            'controller' => 'news',
            'action'     => 'n',
            'date'       => 1,
            'time'       => 2
        ]);
        $router->add('/q/{date:[0-9]{8}}/{time:[0-9]{6}}', [
            'controller' => 'question',
            'action'     => 'q',
            'date'       => 1,
            'time'       => 2
        ]);
        $router->add('/m/{name:[a-zA-Z0-9_]+}', [
            'controller' => 'member',
            'action'     => 'm',
            'name'       => 1
        ]);
        $router->add('/settings', [
            'controller' => 'member',
            'action'     => 'settings'
        ]);
        $router->add('/notifications', [
            'controller' => 'member',
            'action'     => 'notifications'
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
    $di->set('assets', function () {

        return new \Phalcon\Assets\Manager();
    }, true);
    $di->set('mongo', function () use ($config) {

        // '://' fuck me
        $mongo = new MongoClient('mongodb://' . $config->database->username . ':' . $config->database->password . '@' . $config->database->host . ':' . $config->database->port);

        return $mongo->selectDB($config->database->name);
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
        $crypt->setKey($config->application->encryptKey);

//        $crypt->setKey('NewsBiuIsAwesome');

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

    $app = new \Phalcon\Mvc\Application($di);
    echo $app->handle()->getContent();

} catch (\Phalcon\Exception $e) {
    echo $e->getMessage();
}