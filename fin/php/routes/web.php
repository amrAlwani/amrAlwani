<?php
/**
 * مسارات الويب
 */

use App\Core\Router;

$router = new Router();

// الصفحة الرئيسية
$router->get('/', 'HomeController@index');

// صفحات المصادقة
$router->get('login', 'AuthController@showLogin');
$router->get('register', 'AuthController@showRegister');

return $router;
