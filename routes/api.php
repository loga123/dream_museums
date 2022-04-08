<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\API\CityController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\MarkerController;
use App\Http\Controllers\API\PermissionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController as UC;
use App\Http\Controllers\API\GroupCOntroller;
use App\Http\Controllers\API\ExportController;
use Illuminate\Http\Request;

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

Route::get('cities',  [CityController::class, 'index_all'])->name('city.all');

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', [LoginController::class, 'logout']);

    Route::get('user', [UserController::class, 'current']);

    Route::patch('settings/profile', [ProfileController::class, 'update']);
    Route::patch('settings/password', [PasswordController::class, 'update']);
    Route::patch('admin/user/{user}/reset_password', [PasswordController::class, 'reset_password']);
    Route::patch('admin/user/{user}/permissions', [UC::class, 'permissions']);

    Route::resource('city',CityController::class);
    Route::resource('permission',PermissionController::class);
    Route::resource('role',RoleController::class);
    Route::resource('admin/user',UC::class);
    Route::resource('group',GroupCOntroller::class);
    Route::resource('marker',MarkerController::class);

    //get all items
    Route::get('permissions',  [PermissionController::class, 'index_all'])->name('permission.all');
    Route::get('roles',  [RoleController::class, 'index_all'])->name('role.all');
    Route::get('groups',  [GroupCOntroller::class, 'index_all'])->name('group.all');
    Route::get('markers',  [MarkerController::class, 'index_all'])->name('marker.all');

    //post method
    Route::post('markers-group',  [MarkerController::class, 'mergeMarkersWithGroup'])->name('markersGroupMarge');
    Route::post('marker-group-merge',  [MarkerController::class, 'mergeMarkerAndGroupInGroup'])->name('mergeMarkerAndGroupInGroup');
    Route::post('marker/insert-in-group',  [MarkerController::class, 'insertInGroup'])->name('insertInGroup');

    //save marker druga logika - spremanje linka u QR kod
    Route::post('marker_new_store',  [MarkerController::class, 'store2'])->name('markerNewStore');


    Route::delete('marker_group/{marker}/{group}',  [MarkerController::class, 'detach'])->name('markersGroupDetach');

    //DOWNLOAD
     Route::get('group/{group}/export-marker-picture',  [ExportController::class, 'generatePDF'])->name('generatePDF');
     Route::get('marker/{marker}/export-marker-text',  [ExportController::class, 'generatePdfWithText'])->name('generatePdfWithText');

     //check name marker - CLONE marker
     //Route::get('marker/check-name',  [MarkerController::class, 'checkName'])->name('marker.checkName');

    //check name marker - CLONE marker
    Route::get('check-name',  [MarkerController::class, 'checkName'])->name('marker.checkName');
});

Route::group(['middleware' => 'guest:api'], function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [RegisterController::class, 'register']);

    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('password/reset', [ResetPasswordController::class, 'reset']);

    Route::post('email/verify/{user}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('email/resend', [VerificationController::class, 'resend']);

    Route::post('oauth/{driver}', [OAuthController::class, 'redirect']);
    Route::get('oauth/{driver}/callback', [OAuthController::class, 'handleCallback'])->name('oauth.callback');
});
