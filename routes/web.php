<?php

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

// This is an API-only project. The root URL redirects to the Scribe API docs.
Route::get('/', fn () => Redirect::to('/docs'));
