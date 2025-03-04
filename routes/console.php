<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Broadcast;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    return $user->orders()->where('id', $orderId)->exists();
});