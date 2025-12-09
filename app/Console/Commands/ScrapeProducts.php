<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeProductJob;
use App\Models\ProductModel;
use Illuminate\Console\Command;
use Symfony\Component\HttpClient\HttpClient;
use Goutte\Client;
use Illuminate\Support\Facades\Log;

class ScrapeProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scraping data produk dari tokomein.com';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $categories = $this->categoryProduct();
        foreach ($categories as $category) {
            ScrapeProductJob::dispatch($category);
            Log::info("✔ Job dikirim untuk category: {$category}");
        }
        return 0;
    }

    private function categoryProduct()
    {
        $categories = [
            'mesin-makanan',
            'mesin-minuman',
            'mesin-pengemas',
            'mesin-bakery',
            'mesin-baru',
            'alat-dapur-modern',
            'mesin-industri',
            'mesin-pertanian/mesin-cetak-pelet-apung',
            'mesin-pertanian/mesin-grinder-kompos-organik',
            'mesin-pertanian/mesin-oven-pengering',
            'mesin-pertanian/mesin-pasteurisasi',
            'mesin-pertanian/mesin-pelubang-tanah',
            'mesin-pertanian/mesin-pemanen-padi',
            'mesin-pertanian/mesin-pembuat-dodol',
            'mesin-pertanian/mesin-penanam',
            'mesin-pertanian/mesin-pencacah-rumput-chopper',
            'mesin-pertanian/mesin-penepung-diskmill',
            'mesin-pertanian/mesin-penepung-hammer-mill',
            'mesin-pertanian/mesin-pengering-padi',
            'mesin-pertanian/mesin-pengolah-madu',
            'mesin-pertanian/mesin-pengupas-dan-pemipil-jagung',
            'mesin-pertanian/mesin-pengupas-kacang',
            'mesin-pertanian/mesin-pengupas-kopi',
            'mesin-pertanian/mesin-pengupas-kulit-ari',
            'mesin-pertanian/mesin-pengupas-padi',
            'mesin-pertanian/mesin-peternakan',
            'mesin-pertanian/mesin-sangrai-kopi',
            'mesin-pertanian/penetas-telur',
        ];
        return $categories;
    }
}
