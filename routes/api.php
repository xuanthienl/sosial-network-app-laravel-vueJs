<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// post
Route::prefix('/post')->group(function() {
    Route::get('index', 'API\PostsController@index');
    Route::get('{id}/details', 'API\PostsController@show');
    Route::post('store', 'API\PostsController@store');
    Route::post('{id}/update', 'API\PostsController@update');
    Route::post('destroy', 'API\PostsController@destroy');

    Route::post('likes', 'API\PostsController@likes');
    Route::post('comment', 'API\PostsController@comment');
    Route::post('del-comment', 'API\PostsController@deleteComment');
});

Route::post('likes-comment/{id}', 'API\PostsController@likesComment');
Route::post('reply-comment/{id}', 'API\PostsController@replyComment');
Route::get('replies/{id}', 'API\PostsController@getReply');

// user
Route::get('users', 'API\UserController@index');
Route::post('follow', 'API\UserController@follow');
Route::post('unfollow', 'API\UserController@unfollow');

// reset pw
Route::post('reset-pasword', 'API\UserController@resetPassword');

Route::get('user/{username}', 'API\UserController@profile');
Route::post('user/{id}/profile/update', 'API\UserController@profileUpdate');
Route::post('login', 'API\UserController@login');
Route::post('register', 'API\UserController@register');
Route::middleware('auth:api')->group(function () {
    Route::post('logout', 'API\UserController@logout');
    
    Route::post('add-contact', 'API\MessagesController@addContact');
    Route::get('contacts', 'API\MessagesController@contacts');
    Route::get('get-messages-for/{id}', 'API\MessagesController@getMessagesFor');
    Route::post('messages/send', 'API\MessagesController@send');
    Route::get('search-messages', 'API\MessagesController@searchMessages');
});

Route::get('search', 'API\UserController@search');
Route::get('music-mp3', 'API\UserController@music');

//music
Route::prefix('/music')->group(function() {
    Route::get('index', 'API\MusicController@index');
    Route::get('convert-base64', 'API\MusicController@convertBase64');
    Route::post('store', 'API\MusicController@store');
    Route::post('destroy', 'API\MusicController@destroy');

    Route::post('likes', 'API\MusicController@likes');
    Route::post('comment', 'API\MusicController@comment');
    Route::post('del-comment', 'API\MusicController@deleteComment');
});

Route::get('login/google', 'API\UserController@redirectToProvider');
Route::get('login/google/callback', 'API\UserController@handleProviderCallback');