<?php

namespace App\Console;

use App\Jobs\ScrapeProductJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // $categories = [
        //     'mesin-makanan',
        //     'mesin-minuman',
        //     'mesin-pengemas',
        //     'mesin-bakery',
        //     'mesin-baru',
        //     'alat-dapur-modern',
        //     'mesin-industri',
        //     'mesin-pertanian/mesin-cetak-pelet-apung',
        //     'mesin-pertanian/mesin-grinder-kompos-organik',
        //     'mesin-pertanian/mesin-oven-pengering',
        //     'mesin-pertanian/mesin-pasteurisasi',
        //     'mesin-pertanian/mesin-pelubang-tanah',
        //     'mesin-pertanian/mesin-pemanen-padi',
        //     'mesin-pertanian/mesin-pembuat-dodol',
        //     'mesin-pertanian/mesin-penanam',
        //     'mesin-pertanian/mesin-pencacah-rumput-chopper',
        //     'mesin-pertanian/mesin-penepung-diskmill',
        //     'mesin-pertanian/mesin-penepung-hammer-mill',
        //     'mesin-pertanian/mesin-pengering-padi',
        //     'mesin-pertanian/mesin-pengolah-madu',
        //     'mesin-pertanian/mesin-pengupas-dan-pemipil-jagung',
        //     'mesin-pertanian/mesin-pengupas-kacang',
        //     'mesin-pertanian/mesin-pengupas-kopi',
        //     'mesin-pertanian/mesin-pengupas-kulit-ari',
        //     'mesin-pertanian/mesin-pengupas-padi',
        //     'mesin-pertanian/mesin-peternakan',
        //     'mesin-pertanian/mesin-sangrai-kopi',
        //     'mesin-pertanian/penetas-telur',
        // ];
        // $schedule->call(function () use ($categories) {
        //     foreach ($categories as $category) {
        //         dispatch(new ScrapeProductJob($category));
        //     }
        // })->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
