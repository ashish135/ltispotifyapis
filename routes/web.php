<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackController;
use App\Models\Track;
use Illuminate\Http\Request;
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

Route::get('/', function (Request $request) {
    $isrc = $request->input('isrc');
    $tracks = $track = null;
    if($isrc != ""){
        $track = Track::where('isrc', $isrc)->first();
    } else{
        $tracks = Track::all();
    }
    return view('welcome', ['tracks' => $tracks, 'track' => $track]);
});
Route::get('tracks/create', [TrackController::class, 'create'])->name('tracks.create');
Route::post('tracks', [TrackController::class, 'store'])->name('tracks.store');
