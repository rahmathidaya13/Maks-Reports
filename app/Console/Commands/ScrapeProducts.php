<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\HttpClient\HttpClient;
use Goutte\Client;
use App\Models\ScrapedModel;
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
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64)...',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)...',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)...',
            'Mozilla/5.0 (Linux; Android 11; SM-G996B)...',
        ];
        $client = new Client(HttpClient::create([
            'headers' => [
                'User-Agent' => $agents[array_rand($agents)],
            ],
            'timeout' => 20,

        ]));

        $categories = [
            'mesin-minuman',
            'mesin-pengemas',
        ];

        $baseUrl = 'https://www.tokomesin.com/product-category';
        $results = [];

        foreach ($categories as $category) {
            $page = 1;
            $prevFirstProduct = null;

            while (true) {
                $url = $page == 1
                    ? "$baseUrl/$category"
                    : "$baseUrl/$category/page/$page";

                $this->info("Scraping: $url");

                try {
                    $crawler = $client->request('GET', $url);
                } catch (\Exception $e) {
                    Log::error("Request failed: $url - " . $e->getMessage());
                    break;
                }

                $productNodes = $crawler->filter('li.product');
                $countNodes = $productNodes->count();

                // ❌ Jika WordPress mengembalikan halaman kosong → stop
                if ($countNodes == 0) {
                    $this->info("Tidak ada produk lagi. STOP.");
                    break;
                }

                // Ambil produk pertama halaman ini
                $currentFirstProduct = trim(
                    $productNodes->first()->filter('.woocommerce-loop-product__title')->text()
                );

                // ❌ Jika halaman 2/3/4 ternyata sama dengan halaman sebelumnya → infinite loop → STOP
                if ($prevFirstProduct === $currentFirstProduct) {
                    $this->info("Halaman sama dengan sebelumnya. STOP.");
                    break;
                }

                $prevFirstProduct = $currentFirstProduct;

                // Loop semua produk
                $productNodes->each(function ($node) use (&$results, $category) {

                    $namaNode = $node->filter('.woocommerce-loop-product__title');
                    if (!$namaNode->count())
                        return;

                    $nama = trim($namaNode->text());

                    $harga = $node->filter('.price')->count()
                        ? trim($node->filter('.price')->text())
                        : null;

                    $link = $node->filter('a')->first()->attr('href') ?? null;

                    $imgNode = $node->filter('img')->first();
                    $img = $imgNode->attr('data-src')
                        ?? $imgNode->attr('data-lazy-src')
                        ?? $imgNode->attr('src');

                    // ✔ Gunakan updateOrCreate agar tidak ganda
                    try {
                        ScrapedModel::updateOrCreate(
                            ['link' => $link], // key unik
                            [
                                'name' => $nama,
                                'price' => $harga,
                                'image_link' => $img,
                                'category' => $category,
                            ]
                        );
                    } catch (\Throwable $e) {
                        Log::error("Gagal simpan DB ($nama): " . $e->getMessage());
                    }

                    $results[] = [
                        'name' => $nama,
                        'price' => $harga,
                        'link' => $link,
                        'image_link' => $img,
                        'category' => $category
                    ];
                });

                $page++;
                sleep(rand(1, 3));
            }
        }

        Log::info("Scraping selesai. Total data: " . count($results));
        $this->info("Scraping selesai. Total data: " . count($results));
    }
}
