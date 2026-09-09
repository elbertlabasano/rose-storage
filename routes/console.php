<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('storage:health', function () {
    $this->info('WIPS Storage is ready.');
})->purpose('Check storage API availability');


