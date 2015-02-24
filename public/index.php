<?php

/******************************* LOADING & INITIALIZING BASE APPLICATION ****************************************/

// Configuration for error reporting, useful to show every little problem during development
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Load Composer's PSR-4 autoloader (necessary to load Slim, Mini etc.)
require '../vendor/autoload.php';
include '../config.php';

// Initialize Slim (the router/micro framework used)
$app = new \Slim\Slim();

/*****************  ******************/
$app->add(new \Slim\Middleware\SessionCookie(array('secret' => 'ploikwxcervt#hfe%vhj&bt*v9z0ikhtr+_(()_')));

function authen(){
    if (isset($_COOKIE['user']) || $_SESSION['user']) {
        return $_COOKIE['user'];
    }else{
        header('Location: /login');
        exit;
    }
}

function validate(){
    return isset($_COOKIE['user']) ? $_COOKIE['user'] : false ;
}

function getFlash($name){
    return isset($_SESSION['slim.flash'][$name]) ? $_SESSION['slim.flash'][$name] : false ;
}
/*****************  ******************/

// and define the engine used for the view @see http://twig.sensiolabs.org
$app->view = new \Slim\Views\Twig();
$app->view->setTemplatesDirectory("../Mini/view");

/******************************************* THE CONFIGS *******************************************************/

// Configs for mode "development" (Slim's default), see the GitHub readme for details on setting the environment
$app->configureMode('development', function () use ($app) {

    // pre-application hook, performs stuff before real action happens @see http://docs.slimframework.com/#Hooks
    $app->hook('slim.before', function () use ($app) {

        // SASS-to-CSS compiler @see https://github.com/panique/php-sass
        SassCompiler::run("scss/", "css/");

        // CSS minifier @see https://github.com/matthiasmullie/minify
        $minifier = new MatthiasMullie\Minify\CSS('css/style.css');
        $minifier->minify('css/style.css');

        // JS minifier @see https://github.com/matthiasmullie/minify
        // DON'T overwrite your real .js files, always save into a different file
        //$minifier = new MatthiasMullie\Minify\JS('js/application.js');
        //$minifier->minify('js/application.minified.js');
    });

    // Set the configs for development environment
    $app->config(array(
        'debug' => true,
        'database' => array(
            'db_host' => MONGO_HOST,
            'db_port' => MONGO_PORT,
            'db_name' => MONGO_DB,
            'db_user' => MONGO_USER,
            'db_pass' => MONGO_PASS
        ),
        'salt' => SITE_BLOWFISH
    ));
});

// Configs for mode "production"
$app->configureMode('production', function () use ($app) {
    // Set the configs for production environment
    $app->config(array(
        'debug' => false,
        'database' => array(
            'db_host' => '',
            'db_port' => '',
            'db_name' => '',
            'db_user' => '',
            'db_pass' => ''
        )
    ));
});

/******************************************** THE MODEL ********************************************************/

// Initialize the model, pass the database configs. $model can now perform all methods from Mini\model\model.php
$model = new \Mini\Model\Model($app->config('database'));

/**
 * Hook for WHAT?
 */
$app->hook('slim.before.dispatch', function() use ($app) {
    $user = null;
    if (isset($_COOKIE['user'])) {
        $user = $_COOKIE['user'];
    }
    $app->view()->setData('user', $user);
});

/************************************ THE ROUTES / CONTROLLERS *************************************************/

// GET request on homepage, simply show the view template index.twig
$app->get('/', function () use ($app) {

    $user = validate();
    $app->render('index.twig', array(
        'title' => 'Home',
        'user' => $user,
        'error' => getFlash('error'),
    ));
});

/**********************************************************/
/********************** LOGIN PAGE ************************/
/**********************************************************/
$app->get('/login', function () use ($app) {
    if (validate()===false) {
        $app->render('login.twig', array(
            'title' => 'Login to add event',
            'error' => getFlash('error'),
        ));
    }else{
        $app->redirect('/');
    }

});


$routers = glob('../Mini/routers/*.router.php');
foreach ($routers as $router) {
    require $router;
}

//$app->group('/event', 'authen', function () use ($app, $model) {
//
//    $app->get('/', function () use ($app) {
//        $items = $dbmongo->event->find();
//        $events = [];
//        foreach($items as $item){
//            $item['id'] = $item['_id']->{'$id'};
//            unset($item['_id']);
//            $events[] = $item;
//        }
//        $app->render('event/index.twig', array(
//            'events' => $events,
//        ));
//    });
//
//    $app->get('/form', function() use ($app){
//        $app->render('event/form.twig', array(
//            'id' => 0
//        ));
//    });
//
//});


/******************************************* RUN THE APP *******************************************************/

$app->run();

