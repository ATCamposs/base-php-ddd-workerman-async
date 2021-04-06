<?php

/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Webman\Route;

Route::post('/login', 'App\Users\Presentation\Authentication@login');
Route::post('/register', 'App\Users\Presentation\Authentication@register');
Route::get('/activate', 'App\Users\Presentation\Authentication@activate');

Route::group('/users', function () {
    Route::post('/logout', 'App\Users\Presentation\Authentication@logout');
    Route::delete('/delete', 'App\Users\Presentation\Authentication@delete');
})->middleware([support\middleware\CheckLogin::class]);
Route::group('/users/update', function () {
    Route::post('/user_name', 'App\Users\Presentation\Authentication@updateUserName');
    Route::post('/email', 'App\Users\Presentation\Authentication@updateEmail');
    Route::post('/password', 'App\Users\Presentation\Authentication@updatePassword');
})->middleware([support\middleware\CheckLogin::class]);

Route::group('/admin/update', function () {
    Route::post('/user_name', 'App\Users\Presentation\Administration@changeUserUserName');
    Route::post('/email', 'App\Users\Presentation\Administration@changeUserEmail');
    Route::post('/activate', 'App\Users\Presentation\Administration@activateUser');
    Route::post('/access_level', 'App\Users\Presentation\Administration@changeUserAccessLevel');
})->middleware([support\middleware\CheckLogin::class]);
