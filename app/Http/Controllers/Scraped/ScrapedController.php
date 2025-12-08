<?php

namespace App\Http\Controllers\Scraped;

use Goutte\Client;
use App\Models\ScrapedModel;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpClient\HttpClient;

class ScrapedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function scrape()
    {
        $client = new Client(HttpClient::create([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0 Safari/537.36',
            ]
        ]));
        $categories = [
            'mesin-pengemas',
        ];

        $baseUrl = 'https://www.tokomesin.com/product-category';

        $results = [];

        foreach ($categories as $category) {
            $page = 1;
            while (true) {
                $url = $page == 1
                    ? "$baseUrl/$category"
                    : "$baseUrl/$category/page/$page";
                Log::info("Scraping: $url");

                try {
                    $crawler = $client->request('GET', $url);
                } catch (\Exception $e) {
                    // Log error detail, lalu break pagination untuk kategori ini
                    Log::error("Request failed for $url: " . $e->getMessage());
                    break; // jika halaman error
                }

                $itemsFound = 0;
                // Cek apakah ada container produk
                if ($crawler->filter('li.product')->count() === 0) {
                    Log::info("Tidak menemukan selector li.product di: $url");
                    break;
                }
                $crawler->filter('li.product')->each(function ($node) use (&$itemsFound, &$results, $category) {
                    try {
                        // Cek dan ambil title
                        $titleNode = $node->filter('.woocommerce-loop-product__title');
                        $nama = $titleNode->count() ? trim($titleNode->text()) : null;

                        if (!$nama) {
                            // jika tidak ada nama, skip item
                            return;
                        }

                        // Harga (bersihkan whitespace berlebih)
                        $priceNode = $node->filter('.price');
                        $harga = $priceNode->count() ? preg_replace('/\s+/', ' ', trim($priceNode->text())) : null;

                        // Link produk
                        $linkNode = $node->filter('a')->first();
                        $link = $linkNode->count() ? $linkNode->attr('href') : null;

                        // Gambar (cek data-src / data-lazy-src / src)
                        $imgNode = $node->filter('img')->first();
                        $img = null;
                        if ($imgNode->count()) {
                            $img = $imgNode->attr('data-src') ?? $imgNode->attr('data-lazy-src') ?? $imgNode->attr('src') ?? null;
                        }

                        // Tanda berhasil menemukan item
                        $itemsFound++;

                        // Simpan ke DB — bungkus try/catch agar tidak menghentikan scraping
                        try {
                            ScrapedModel::create([
                                'name' => $nama,
                                'price' => $harga,
                                'link' => $link,
                                'image_link' => $img,
                                'category' => $category,
                            ]);
                        } catch (\Throwable $e2) {
                            // Log detail error penyimpanan (mis. fillable/DB)
                            Log::error('DB save failed for item: ' . $nama . ' - ' . $e2->getMessage());
                        }

                        // Tambah ke hasil respons (optional)
                        $results[] = [
                            'name' => $nama,
                            'price' => $harga,
                            'link' => $link,
                            'image_link' => $img,
                            'category' => $category
                        ];
                    } catch (\Throwable $e) {
                        // Jika parsing satu item gagal, log dan lanjut
                        Log::warning('Parse item failed: ' . $e->getMessage());
                    }
                });

                if ($itemsFound == 0) {
                    break; // pagination habis
                }

                $page++;
                sleep(1); // SOPAN

            }
        }
        return response()->json([
            'status' => true,
            'message' => "Scraping selesai",
            'total' => count($results),
            'data' => $results
        ]);

    }


}
