<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;




Route::get('/', 'HomeController@index')->name('welcome');
Route::get('/collection', 'HomeController@collection')->name('collection');
Route::get('/projets', 'HomeController@projet')->name('projet');
Route::get('/opportinuites', 'HomeController@opportinuite')->name('opportinuite');
// Liker un song
Route::get('/songs/{song:slug}/like', 'LikeController@songLike')->name('likeSong');
// Uploads d'avatars des profil d'utilisateurs
Route::post('/avatar', 'AvatarController@uploadAvatar')->name('avatar');

Route::name('artist.')->group(function () {
    // Autrhentification des artists
    Route::post('/login/artist', 'Artist\Auth\ArtistLoginController@login')->name('login');
    Route::post('/register/artist', 'Artist\Auth\ArtistRegisterController@register')->name('register');
    Route::post('artist/logout', 'Artist\Auth\ArtistLoginController@logout')->name('logout');
    // Authentication artist on facebook
    Route::get('artist/login/{provider}', 'Artist\Auth\ArtistLoginController@redirectToProvider')->name('loginFacebook');
    Route::get('artist/login/{provider}/callback', 'Artist\Auth\ArtistLoginController@handleProviderCallback');

    // Vue du profile de l'artiste
    Route::get('/artists/{artist:slug}', 'Artist\ArtistController@profile')->name('profile');
    // Vue d'un son de l'artiste
    Route::get('/artists/{artist:slug}/{song:slug}', 'Artist\ArtistController@oneSong')->name('song');

    // Follower un artist
    Route::post('/artist/{artist:slug}/follow', 'Artist\ArtistController@follow')->name('follow');
    
    Route::middleware('auth:artist')->prefix('/artist')->group(function() {
        // Artist Dashboard
        Route::get('/home', 'Artist\ArtistController@index')->name('index');
        Route::get('/opportinuites', 'Artist\ArtistController@opportinuites')->name('opportinuite');
        Route::get('/setting', 'Artist\ArtistController@setting')->name('setting');
        // Routes pour les type d'artist
        Route::post('/typeartists/store', 'Artist\TypeArtistController@storeTypeArtist')->name('typeartists.store');
        // Ajout de son
        Route::post('/song/add', 'Artist\SongController@store')->name('addSong');
        Route::delete('/songs/{song:slug}/delete', 'Artist\SongController@delete')->name('deleteSong');
       
    });
});

Route::name('user.')->group(function () {
    // Autrhentification des utilisteurs
    Auth::routes();

    Route::get('login/{provider}', 'Auth\LoginController@redirectToProvider')->name('loginFacebook');
    Route::get('login/{provider}/callback', 'Auth\LoginController@handleProviderCallback');

});

Route::name('admin.')->prefix('/admin')->group(function () {
    // Autrhentification des Admins
    Route::get('/login', 'Admin\Auth\AdminLoginController@showLoginForm')->name('login');
    Route::post('/login', 'Admin\Auth\AdminLoginController@login')->name('login');
    Route::post('/logout', 'Admin\Auth\AdminLoginController@logout')->name('logout');


    Route::middleware('auth:admin')->group(function() {
        Route::get('/', 'Admin\AdminController@index')->name('index');
        // Routes for sevices CRUD
        Route::get('/services', 'Admin\ServicesController@index')->name('services.index');
        Route::get('/services/create', 'Admin\ServicesController@create')->name('services.create');
        Route::post('/services/store', 'Admin\ServicesController@store')->name('services.store');
        Route::get('/services/{service:slug}/edit', 'Admin\ServicesController@edit')->name('services.edit');
        Route::put('/services/{service:slug}', 'Admin\ServicesController@update')->name('services.update');
        Route::delete('/services/{service:slug}/delete', 'Admin\ServicesController@delete')->name('services.delete');
        // Routes for artists CRUD
        Route::get('/artists', 'Admin\ArtistController@index')->name('artists.index');
        Route::post('/typeartists', 'Admin\ArtistController@storeTypeArtist')->name('typeartists.create');
        Route::delete('/typesartists/delete/{id}', 'Admin\ArtistController@deleteTypeArtist')->name('typeartists.delete');
    });
});


Route::get('/sons', function () {
    $sons = ['Music' => 'http://localhost:8000/user/sons/music.mp3'];
    $fileContents = Storage::disk('public')->get('sons/music.mp3');
    // dd($fileContents);
    $response = Response::make($fileContents, 200);
    $response->header('Content-Type', "audio/mpeg");
    
    return $response;
    // return response()->json($sons);
});



