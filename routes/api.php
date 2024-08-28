<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
	
	$timeUp = \Carbon\Carbon::now()->addMinutes(5);

	$lastTimeExecutedCronjob = null;

	$databaseIsUp = false;

	// Check if database is up

	try {

		DB::connection()->getPdo();

		$databaseIsUp = true;

		$lastTimeExecutedCronjob = \App\Models\ProductImportControl::orderBy('created_at', 'desc')->first();

	} catch (Throwable $e) {

		$databaseIsUp = false;

	}



	return response()->json([
		"message" => "Welcome to the API",
		"database_is_up" => $databaseIsUp,
		"last_time_executed_cronjob" => !$lastTimeExecutedCronjob ? $lastTimeExecutedCronjob : $lastTimeExecutedCronjob->created_at,
		"server_time" => \Carbon\Carbon::now(),
		"server_time_up" => $timeUp,
		"memory_usage" => round(memory_get_usage() / 1024 / 1024,2) . " MB",
	]);

});

Route::prefix('products')->group(function () {
	Route::get('/', [ProductController::class, 'index']);
	Route::get('/{code}', [ProductController::class, 'show'])->whereNumber('code');
	Route::put('/{code}', [ProductController::class, 'update'])->whereNumber('code');
	Route::delete('/{code}', [ProductController::class, 'destroy'])->whereNumber('code');
	Route::get('/update', [ProductController::class, 'updateProducts']);
});
