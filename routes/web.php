<?php

use Illuminate\Support\Facades\Route;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', 'BuscaController@index');
Route::get('/ajax/produtos', 'BuscaController@produtosAjax');
