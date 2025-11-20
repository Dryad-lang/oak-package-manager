<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'matematica-utils',
                'display_name' => 'Matematica Utils',
                'description' => 'Biblioteca completa de utilitarios matematicos para Dryad',
                'author' => 'Dryad Community',
                'author_email' => 'community@dryad-lang.org',
                'license' => 'MIT',
                'keywords' => json_encode(['matematica', 'algebra', 'geometria']),
                'homepage' => 'https://github.com/dryad-lang/matematica-utils',
                'repository' => json_encode([
                    'type' => 'git',
                    'url' => 'https://github.com/dryad-lang/matematica-utils.git'
                ]),
                'download_count' => 1250,
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($packages as $package) {
            $packageId = DB::table('packages')->insertGetId($package);
            $this->createVersionsForPackage($packageId, $package['name']);
        }
    }

    private function createVersionsForPackage(int $packageId, string $packageName): void
    {
        $versions = [
            [
                'package_id' => $packageId,
                'version' => '1.0.0',
                'changelog' => 'Versao inicial',
                'dependencies' => json_encode([]),
                'dev_dependencies' => json_encode([]),
                'download_url' => "https://example.com/{$packageName}/1.0.0.tar.gz",
                'file_hash' => hash('sha256', $packageName . '1.0.0'),
                'file_size' => 15000,
                'download_count' => 100,
                'published_at' => Carbon::now()->subDays(30),
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(30),
            ],
        ];

        DB::table('package_versions')->insert($versions);
    }
}