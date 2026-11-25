<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

// Customer photos uploaded to a cart that never became an order.
Schedule::call(function () {
    $disk = Storage::disk('local');
    foreach ($disk->files('cart-uploads') as $file) {
        if ($disk->lastModified($file) < now()->subDays(3)->getTimestamp()) {
            $disk->delete($file);
        }
    }
})->daily()->name('cleanup-cart-uploads');
