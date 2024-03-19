<?php

use App\Models\cities;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('cities', function (Request $request) {
    return cities::pluck('name', 'id')->toArray();
});

Route::get('post', function () {
    // return Post::with(['country:id,name', 'city:id,name'])->get();
    return Post::with(['country:id,name', 'city:id,name'])->get()->map(function ($post) {
        unset($post->countries);
        unset($post->cities);

        return $post;
    });
});
