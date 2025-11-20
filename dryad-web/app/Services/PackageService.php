<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PackageService
{
    private $registryUrl;
    private $timeout;

    public function __construct()
    {
        $this->registryUrl = config('services.dryad_registry.url', 'http://127.0.0.1:4000');
        $this->timeout = config('services.dryad_registry.timeout', 10);
    }

    /**
     * Get all packages from registry
     */
    public function getAllPackages($search = null, $sort = 'popularity', $limit = 20, $offset = 0)
    {
        $cacheKey = "packages_{$search}_{$sort}_{$limit}_{$offset}";
        
        return Cache::remember($cacheKey, 300, function () use ($search, $sort, $limit, $offset) {
            try {
                $response = Http::timeout($this->timeout)->get("{$this->registryUrl}/api/packages", [
                    'search' => $search,
                    'sort' => $sort,
                    'limit' => $limit,
                    'offset' => $offset
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('Failed to fetch packages from registry', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return $this->getFallbackPackages();
            } catch (\Exception $e) {
                Log::error('Exception fetching packages from registry', [
                    'error' => $e->getMessage()
                ]);

                return $this->getFallbackPackages();
            }
        });
    }

    /**
     * Get a specific package by name
     */
    public function getPackage($name)
    {
        $cacheKey = "package_{$name}";
        
        return Cache::remember($cacheKey, 300, function () use ($name) {
            try {
                $response = Http::timeout($this->timeout)->get("{$this->registryUrl}/api/packages/{$name}");

                if ($response->successful()) {
                    return $response->json();
                }

                if ($response->status() === 404) {
                    return null;
                }

                Log::error('Failed to fetch package from registry', [
                    'package' => $name,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('Exception fetching package from registry', [
                    'package' => $name,
                    'error' => $e->getMessage()
                ]);

                return null;
            }
        });
    }

    /**
     * Search packages
     */
    public function searchPackages($query, $sort = 'relevance', $limit = 20)
    {
        $cacheKey = "search_{$query}_{$sort}_{$limit}";
        
        return Cache::remember($cacheKey, 300, function () use ($query, $sort, $limit) {
            try {
                $response = Http::timeout($this->timeout)->get("{$this->registryUrl}/api/search", [
                    'q' => $query,
                    'sort' => $sort,
                    'limit' => $limit
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('Failed to search packages in registry', [
                    'query' => $query,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return ['packages' => [], 'total' => 0];
            } catch (\Exception $e) {
                Log::error('Exception searching packages in registry', [
                    'query' => $query,
                    'error' => $e->getMessage()
                ]);

                return ['packages' => [], 'total' => 0];
            }
        });
    }

    /**
     * Get registry statistics
     */
    public function getStats()
    {
        return Cache::remember('registry_stats', 600, function () {
            try {
                $response = Http::timeout($this->timeout)->get("{$this->registryUrl}/api/stats");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('Failed to fetch stats from registry', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return $this->getFallbackStats();
            } catch (\Exception $e) {
                Log::error('Exception fetching stats from registry', [
                    'error' => $e->getMessage()
                ]);

                return $this->getFallbackStats();
            }
        });
    }

    /**
     * Get featured packages (most popular)
     */
    public function getFeaturedPackages($limit = 6)
    {
        $cacheKey = "featured_packages_{$limit}";
        
        return Cache::remember($cacheKey, 600, function () use ($limit) {
            $result = $this->getAllPackages(null, 'popularity', $limit, 0);
            return $result['packages'] ?? [];
        });
    }

    /**
     * Check registry health
     */
    public function checkHealth()
    {
        try {
            $response = Http::timeout(5)->get("{$this->registryUrl}/api/health");
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Registry health check failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Fallback packages when registry is unavailable
     */
    private function getFallbackPackages()
    {
        return [
            'packages' => [
                [
                    'name' => 'matematica-utils',
                    'version' => '1.1.0',
                    'description' => 'Biblioteca completa de utilitários matemáticos para Dryad',
                    'author' => 'Dryad Community',
                    'downloads' => 5247,
                    'weeklyDownloads' => 156,
                    'keywords' => ['matematica', 'algebra', 'geometria'],
                    'updated_at' => now()->subDays(2)->toISOString()
                ]
            ],
            'total' => 1,
            'limit' => 20,
            'offset' => 0
        ];
    }

    /**
     * Fallback stats when registry is unavailable
     */
    private function getFallbackStats()
    {
        return [
            'total_packages' => 1,
            'total_downloads' => 5247,
            'weekly_downloads' => 156,
            'total_users' => 524
        ];
    }

    /**
     * Clear cache for packages
     */
    public function clearCache()
    {
        Cache::flush();
    }
}