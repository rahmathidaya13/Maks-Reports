<?php

namespace App\Jobs;

use Goutte\Client;
use App\Models\ProductModel;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\HttpClient\HttpClient;

class ScrapeProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $category;
    public $baseUrl;
    public $results = [];
    public function __construct($category)
    {
        $this->category = $category;
        $this->baseUrl = 'https://www.tokomesin.com/product-category';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // === CLIENT DENGAN TIMEOUT, RETRY, REFERER ===
        $client = new Client(HttpClient::create([
            'headers' => [
                'User-Agent' => $this->randomUserAgent(),
                'Referer' => 'https://www.google.com',
                'Accept-Language' => 'en-US,en;q=0.9',
            ],
            'timeout' => 15,
        ]));

        Log::info("\n=== SCRAPING CATEGORY: $this->category ===");
        $page = 1;
        $prevFirstProduct = null;

        // Loop halaman kategori
        while (true) {
            $url = $page == 1
                ? "$this->baseUrl/$this->category"
                : "$this->baseUrl/$this->category/page/$page";

            Log::info("Scraping: $url");

            // === REQUEST PAGE ===
            try {
                $crawler = $client->request('GET', $url);
            } catch (\Exception $e) {
                Log::error("Request failed: $url - " . $e->getMessage());
                break;
            }

            // === PROCESS PRODUCTS ===
            $productNodes = $crawler->filter('li.product');
            if ($productNodes->count() === 0) {
                Log::info("Tidak ada produk lagi di category {$this->category}. STOP.");
                break;
            }
            // === CEGAH PAGINATION LOOPING ===
            $currentFirstProduct = trim(
                $productNodes->first()->filter('.woocommerce-loop-product__title')->text()
            );

            // Cegah infinite loop
            if ($prevFirstProduct === $currentFirstProduct) {
                Log::info("Halaman sama → STOP ({$this->category})");
                break;
            }

            $prevFirstProduct = $currentFirstProduct;

            // ================================================================
            //  LOOP PRODUK
            // ================================================================
            $productNodes->each(function ($node) use ($client) {

                // nama produk
                $namaNode = $node->filter('.woocommerce-loop-product__title');
                if (!$namaNode->count()) return;
                $nama = trim($namaNode->text());
                // end nama produk

                // --- harga produk ---
                $hargaNode = $node->filter('.price');
                $priceOriginal = null;
                $priceDiscount = null;
                if ($hargaNode->count()) {
                    $origNode = $hargaNode->filter('del .woocommerce-Price-amount');
                    $discNode = $hargaNode->filter('ins .woocommerce-Price-amount');

                    if ($origNode->count())
                        $priceOriginal = (int) preg_replace('/[^\d]/', '', $origNode->text());

                    if ($discNode->count())
                        $priceDiscount = (int) preg_replace('/[^\d]/', '', $discNode->text());

                    if (!$priceOriginal && !$priceDiscount) {
                        $priceOriginal = (int) preg_replace('/[^\d]/', '', $hargaNode->text());
                    }
                }
                // --- end harga produk ---

                // --- link produk ---
                $link = $node->filter('a')->first()->attr('href') ?? null;
                // --- end link produk ---

                // gambar utama
                $imgNode = $node->filter('img')->first();
                $img = $imgNode->attr('data-src')
                    ?? $imgNode->attr('data-lazy-src')
                    ?? $imgNode->attr('src');
                // end gambar utama

                // --- gallery images ---
                $galleryImages = $this->scrapeGallery($client, $link);
                // --- end gallery images ---

                // ✔ Gunakan updateOrCreate agar tidak ganda
                try {
                    ProductModel::updateOrCreate(
                        ['link' => $link], // key unik
                        [
                            'name' => $nama,
                            'price_original' => $priceOriginal,
                            'price_discount' => $priceDiscount,
                            'image_link' => $img,
                            'image_url' => $galleryImages,
                            'category' => $this->category,
                        ]
                    );
                    Log::info("Produk: $nama");
                    Log::info(" → Gallery: " . count($galleryImages) . " gambar");
                    // You can now use $galleryImages as needed, for example, save to database or log
                } catch (\Throwable $e) {
                    Log::error("Gagal simpan ($nama): " . $e->getMessage());
                }
                $this->results[] = [
                    'name' => $nama,
                    'price_original' => $priceOriginal,
                    'price_discount' => $priceDiscount,
                    'link' => $link,
                    'image_link' => $img,
                    'image_url' => $galleryImages,
                    'category' => $this->category,
                ];
            });
            $page++;
            sleep(rand(1, 3));
        }
        Log::info("\nScraping selesai. Total data: " . count($this->results));
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
            $src =  $node->attr('data-src') ??
                $node->attr('data-large_image') ??
                $node->attr('data-lazy-src') ??
                $node->attr('src');

            if ($src) $images[] = $src;
        });

        return array_unique($images);
    }

    private function randomUserAgent()
    {
        // === ROTATING USER AGENT ===
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14 Safari/605.1.15',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148',
            'Mozilla/5.0 (Linux; Android 11; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0 Mobile Safari/537.36',
        ];
        return $agents[array_rand($agents)];
    }
}
