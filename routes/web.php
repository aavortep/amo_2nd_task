<?php

use Illuminate\Support\Facades\Route;
define('SMARTY_DIR', '../vendor/smarty/smarty/libs/');
require_once(SMARTY_DIR . 'Smarty.class.php');


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $smarty = new Smarty();
    $smarty->template_dir = '../resources/views/';
    $smarty->display('index.tpl');
    //return view('welcome');
});
