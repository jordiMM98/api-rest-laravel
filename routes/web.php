<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//cargando clases
use App\Http\Middleware\ApiAuthMiddleware;

Route::get('/', function () {
    return '<h1>Hola munod con laravel</h1>';
});
Route::get('/welcome', function () {
    return view('welcome');
});
Route::get('/pruebas/{nombre?}', function($nombre = null){
	$texto ='<h2>Texto desde una ruta</h2>';
	$texto .= 'Nombre: '.$nombre;

	return view('pruebas',array(
		'texto' => $texto
	));
});
/*Metodos HTTP comunes
*Get :conseguir datos o recursos
*POST: Guardr datos o recursos o hacer  logica desde formulario
*PUT:Actualicar recursos o datos 
*Delete:Eliminar datos o recursos

*/
Route::get('/animales','PruebasController@index');
Route::get('/test-orm','PruebasController@testOrm');
//Rutas del api
//ruttas de prueba
//Route::get('/usuario/pruebas','UserController@pruebas');
//Route::get('/categoria/pruebas','CategoryController@pruebas');
//Route::get('/entrada/pruebas','PostController@pruebas');
//Rutas del controlador de usuarios
Route::post('/api/register','UserController@register');
Route::post('/api/login','UserController@login');
Route::put('/api/user/update','UserController@update');
Route::post('/api/user/upload','UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('/api/user/avatar/{filename}', 'UserController@getImage');
Route::get('/api/user/detail/{id}', 'UserController@detail');

///Rutas del controlador de categorias
Route::resource('/api/category','CategoryController');

//Rutas del controldor de entradas
Route::resource('/api/post','PostController');
//nota no tenian /
Route::post('/api/post/upload','PostController@upload');
Route::get('/api/post/image/{filename}','PostController@getImage');
Route::get('/api/post/category/{id}','PostController@getPostByCategory');
Route::get('/api/post/user/{id}','PostController@getPostsByUser');
