<?php

namespace Database\Seeders;

use App\Models\BranchesModel;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branchesData = [
            'Jakarta (Cakung)' => [
                '081318887231',
                '08123386165',
                '081233144166',
                '082140003355',
                '081222218616',
                '081311155633',
                '085319342111',
                '081310495079',
                '081318229800'
            ],
            'Jakarta (Pulogadung)' => [
                '081219000714',
                '081219000713',
                '08135045553'
            ],
            'Jakarta (Jagakarsa)' => [
                '08135045553'
            ],
            'Bekasi' => [
                '081218612009',
                '081242422289',
                '081218612008'
            ],
            'Bandung' => [
                '082214002302',
                '081313091383',
                '082214002303'
            ],
            'Bogor' => [
                '081294928652',
                '081294928653',
                '081385715719',
                '081293981113'
            ],
            'Malang' => [
                '081216665445',
                '081216665454',
                '081333394799'
            ],
            'Surabaya' => [
                '081282993727',
                '081233144177',
                '081357999261'
            ],
            'Blitar' => [
                '081334300036',
                '081334300038',
                '081331709910'
            ],
            'Yogyakarta' => [
                '085200667148',
                '081326870111',
                '081282993447'
            ],
            'Semarang' => [
                '081224422653',
                '082123477709',
                '081224422563'
            ],
            'Palembang' => [
                '081367407078',
                '081281268272',
                '081367407079'
            ],
            'Medan' => [
                '081314025406',
                '081314025405',
                '081384196933',
                '082167734018'
            ],
            'Pekanbaru' => [
                '082321950002',
                '082111443284',
                '081311177522'
            ],
            'Makassar (Perintis)' => [
                '082292249005',
                '081354485755',
                '082260000883',
                '081356680616',
                '081219430954'
            ],
            'Banjarmasin' => [
                '081253323982',
                '082124473388'
            ],
            'Denpasar' => [
                '082236620334',
                '082236620335',
                '082144025417'
            ],
        ];

        foreach ($branchesData as $branchName => $phones) {
            // Tentukan status official
            $statusOfficial = ($branchName === 'Jakarta (Cakung)') ? 'official' : 'unofficial';
            // 1. Buat atau Ambil Data Cabang (Parent)
            $branch = BranchesModel::firstOrCreate(
                ['name' => $branchName],
                [
                    'branches_id' => Str::uuid(),
                    'branch_code' => BranchesModel::generateUniqueCode(),
                    'name'        => $branchName,
                    'address'     => '-',
                    'status_official' => $statusOfficial
                ]
            );

            // 2. Loop nomor telepon dan insert via relasi
            foreach ($phones as $phone) {
                // Saya gunakan firstOrCreate agar jika seeder dijalankan 2x, nomor tidak duplikat
                // Pastikan ganti 'phone_number' sesuai nama kolom di tabel branch_phones kamu
                $branch->branchPhone()->firstOrCreate(
                    ['phone' => $phone],
                );
            }
        }
    }
}
