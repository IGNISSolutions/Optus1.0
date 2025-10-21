<?php

error_reporting(0);

use \Carbon\Carbon;

setLanguage(($_SESSION['lang'] ? $_SESSION['lang'] : config('app.language')));

$lang_file = Carbon::getLocale() . '/lang.php';
if (@include_once($lang_file)) {
    require_once $lang_file;
}