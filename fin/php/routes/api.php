<?php
/**
 * مسارات API
 */

use App\Core\Router;

$router = new Router();

// ===== Auth =====
$router->post('api/auth/register', 'AuthController@register');
$router->post('api/auth/login', 'AuthController@login');
$router->post('api/auth/logout', 'AuthController@logout');
$router->get('api/auth/profile', 'AuthController@profile');

// ===== Products =====
$router->get('api/products', 'ProductController@index');
$router->get('api/products/{id}', 'ProductController@show');
$router->post('api/products', 'ProductController@store');
$router->put('api/products/{id}', 'ProductController@update');
$router->delete('api/products/{id}', 'ProductController@destroy');

// ===== Categories =====
$router->get('api/categories', 'CategoryController@index');
$router->get('api/categories/{id}', 'CategoryController@show');

// ===== Cart =====
$router->get('api/cart', 'CartController@index');
$router->post('api/cart/add', 'CartController@add');
$router->put('api/cart/{id}', 'CartController@update');
$router->delete('api/cart/{id}', 'CartController@remove');

// ===== Orders =====
$router->get('api/orders', 'OrderController@index');
$router->get('api/orders/{id}', 'OrderController@show');
$router->post('api/orders', 'OrderController@store');

return $router;
