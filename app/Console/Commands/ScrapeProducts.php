<?php

namespace App\Console\Commands;

use App\Models\ProductModel;
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


        $baseUrl = 'https://www.tokomesin.com/product-category';
        $results = [];

        foreach ($categories as $category) {
            $this->info("\n=== SCRAPING CATEGORY: $category ===");

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
                if ($productNodes->count() === 0) {
                    $this->info("Tidak ada produk lagi. STOP");
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
                $productNodes->each(function ($node) use (&$results, $category, &$client) {

                    $namaNode = $node->filter('.woocommerce-loop-product__title');
                    if (!$namaNode->count())
                        return;

                    $nama = trim($namaNode->text());


                    $hargaNode = $node->filter('.price');
                    $priceOriginal = null;
                    $priceDiscount = null;

                    // Cek apakah ada harga diskon
                    if ($hargaNode->count()) {

                        // harga original (del)
                        $priceOriginalNode = $hargaNode->filter('del .woocommerce-Price-amount');
                        if ($priceOriginalNode->count()) {
                            $tmp = preg_replace('/[^\d]/', '', $priceOriginalNode->text());
                            $priceOriginal = (int) $tmp;
                        }

                        // harga diskon (ins)
                        $priceDiscountNode = $hargaNode->filter('ins .woocommerce-Price-amount');
                        if ($priceDiscountNode->count()) {
                            $tmp = preg_replace('/[^\d]/', '', $priceDiscountNode->text());
                            $priceDiscount = (int) $tmp;
                        }

                        // kalau tidak ada diskon
                        if (!$priceOriginal && !$priceDiscount) {
                            // fallback: ambil .price langsung
                            $tmp = preg_replace('/[^\d]/', '', $hargaNode->text());
                            $priceOriginal = (int) $tmp;
                        }
                    }
                    // --- link produk ---
                    $link = $node->filter('a')->first()->attr('href') ?? null;
                    // --- gambar utama ---
                    $imgNode = $node->filter('img')->first();
                    $img = $imgNode->attr('data-src')
                        ?? $imgNode->attr('data-lazy-src')
                        ?? $imgNode->attr('src');
                    // --- gallery images ---
                    $galleryImages = $this->scrapeGallery($client, $link);
                    // ✔ Gunakan updateOrCreate agar tidak ganda
                    try {
                        ProductModel::updateOrCreate(
                            ['link' => $link], // key unik
                            [
                                'name' => $nama,
                                'price_original' => $priceOriginal,
                                'price_discount' => $priceDiscount,
                                'image_link' => $img,
                                'image_url' => json_encode($galleryImages),
                                'category' => $category,
                            ]
                        );
                        $this->info("Produk: $nama");
                        $this->info(" → Gallery: " . count($galleryImages) . " gambar");
                        // You can now use $galleryImages as needed, for example, save to database or log
                    } catch (\Throwable $e) {
                        Log::error("Gagal simpan DB ($nama): " . $e->getMessage());
                    }

                    $results[] = [
                        'name' => $nama,
                        'price_original' => $priceOriginal,
                        'price_discount' => $priceDiscount,
                        'link' => $link,
                        'image_link' => $img,
                        'image_url' => json_encode($galleryImages),
                        'category' => $category,
                    ];
                });

                $page++;
                sleep(rand(1, 3));
            }
        }

        Log::info("Scraping selesai. Total data: " . count($results));
        $this->info("Scraping selesai. Total data: " . count($results));
    }

    private function scrapeGallery($client, $url)
    {
        try {
            $crawler = $client->request('GET', $url);
        } catch (\Exception $e) {
            return [];
        }

        $images = [];

        $crawler->filter('.woocommerce-product-gallery__wrapper img')->each(function ($node) use (&$images) {
            $src = $node->attr('data-src')
                ?? $node->attr('data-large_image')
                ?? $node->attr('src');

            if ($src) $images[] = $src;
        });

        return array_unique($images);
    }
}
