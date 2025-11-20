<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ForgejoService
{
    protected string $baseUrl;
    protected string $token;
    protected string $owner;

    public function __construct()
    {
        $this->baseUrl = env('FORGEJO_API_URL', 'http://forgejo:3000/api/v1');
        $this->token = env('FORGEJO_TOKEN', '');
        $this->owner = env('FORGEJO_OWNER', 'dryad-packages');
    }

    /**
     * Create or update a repository for a package
     */
    public function createOrUpdateRepository(string $packageName, string $description): array
    {
        try {
            // Check if repository exists
            $response = Http::withHeaders([
                'Authorization' => 'token ' . $this->token,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/repos/{$this->owner}/{$packageName}");

            if ($response->successful()) {
                // Repository exists, return info
                $repo = $response->json();
                return [
                    'success' => true,
                    'repository_url' => $repo['html_url'] ?? '',
                    'clone_url' => $repo['clone_url'] ?? '',
                ];
            } else if ($response->status() === 404) {
                // Repository doesn't exist, create it
                $createResponse = Http::withHeaders([
                    'Authorization' => 'token ' . $this->token,
                    'Content-Type' => 'application/json',
                ])->post("{$this->baseUrl}/orgs/{$this->owner}/repos", [
                    'name' => $packageName,
                    'description' => $description,
                    'private' => false,
                    'auto_init' => true,
                ]);

                if ($createResponse->successful()) {
                    $repo = $createResponse->json();
                    return [
                        'success' => true,
                        'repository_url' => $repo['html_url'] ?? '',
                        'clone_url' => $repo['clone_url'] ?? '',
                    ];
                } else {
                    Log::error("Failed to create repository", [
                        'status' => $createResponse->status(),
                        'body' => $createResponse->body()
                    ]);
                    return [
                        'success' => false,
                        'error' => 'Failed to create repository: ' . $createResponse->body()
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Unexpected response: ' . $response->body()
            ];

        } catch (Exception $e) {
            Log::error("Forgejo repository operation error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a version tag and upload files
     */
    public function createVersion(string $packageName, string $version, array $files, array $metadata): array
    {
        try {
            // Create package.json with metadata
            $packageJson = [
                'name' => $packageName,
                'version' => $version,
                'description' => $metadata['description'] ?? '',
                'author' => $metadata['author'] ?? '',
                'license' => $metadata['license'] ?? '',
                'homepage' => $metadata['homepage'] ?? '',
                'repository' => $metadata['repository'] ?? '',
                'keywords' => $metadata['keywords'] ?? [],
                'dependencies' => $metadata['dependencies'] ?? [],
            ];

            // Add package.json to files
            $allFiles = array_merge($files, [
                [
                    'path' => 'package.json',
                    'content' => json_encode($packageJson, JSON_PRETTY_PRINT)
                ]
            ]);

            // Create files in the repository using the contents API
            foreach ($allFiles as $file) {
                $this->createOrUpdateFile($packageName, $file['path'], $file['content'], "Add {$file['path']} for version {$version}");
            }

            // Create a release/tag for this version
            $releaseResponse = Http::withHeaders([
                'Authorization' => 'token ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/repos/{$this->owner}/{$packageName}/releases", [
                'tag_name' => "v{$version}",
                'name' => "Version {$version}",
                'body' => $metadata['description'] ?? "Release version {$version}",
                'draft' => false,
                'prerelease' => false,
            ]);

            if ($releaseResponse->successful()) {
                $release = $releaseResponse->json();
                return [
                    'success' => true,
                    'download_url' => $release['tarball_url'] ?? '',
                    'release_url' => $release['html_url'] ?? '',
                ];
            } else {
                Log::error("Failed to create release", [
                    'status' => $releaseResponse->status(),
                    'body' => $releaseResponse->body()
                ]);
                return [
                    'success' => false,
                    'error' => 'Failed to create release: ' . $releaseResponse->body()
                ];
            }

        } catch (Exception $e) {
            Log::error("Forgejo version creation error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create or update a file in the repository
     */
    protected function createOrUpdateFile(string $packageName, string $filePath, string $content, string $message): bool
    {
        try {
            // First, try to get the file to see if it exists
            $getResponse = Http::withHeaders([
                'Authorization' => 'token ' . $this->token,
            ])->get("{$this->baseUrl}/repos/{$this->owner}/{$packageName}/contents/{$filePath}");

            $data = [
                'message' => $message,
                'content' => base64_encode($content),
            ];

            if ($getResponse->successful()) {
                // File exists, update it
                $fileInfo = $getResponse->json();
                $data['sha'] = $fileInfo['sha'];
            }

            $response = Http::withHeaders([
                'Authorization' => 'token ' . $this->token,
                'Content-Type' => 'application/json',
            ])->put("{$this->baseUrl}/repos/{$this->owner}/{$packageName}/contents/{$filePath}", $data);

            return $response->successful();

        } catch (Exception $e) {
            Log::error("Failed to create/update file {$filePath}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get package information
     */
    public function getPackageInfo(string $packageName): array
    {
        try {
            // Get repository info
            $repoResponse = Http::withHeaders([
                'Authorization' => 'token ' . $this->token,
            ])->get("{$this->baseUrl}/repos/{$this->owner}/{$packageName}");

            if (!$repoResponse->successful()) {
                return [
                    'success' => false,
                    'error' => 'Package not found'
                ];
            }

            $repo = $repoResponse->json();

            // Get releases/tags
            $releasesResponse = Http::withHeaders([
                'Authorization' => 'token ' . $this->token,
            ])->get("{$this->baseUrl}/repos/{$this->owner}/{$packageName}/releases");

            $versions = [];
            if ($releasesResponse->successful()) {
                $releases = $releasesResponse->json();
                foreach ($releases as $release) {
                    $versions[] = [
                        'version' => str_replace('v', '', $release['tag_name']),
                        'created_at' => $release['created_at'],
                        'download_url' => $release['tarball_url'],
                    ];
                }
            }

            return [
                'success' => true,
                'data' => [
                    'name' => $packageName,
                    'description' => $repo['description'] ?? '',
                    'repository_url' => $repo['html_url'] ?? '',
                    'clone_url' => $repo['clone_url'] ?? '',
                    'versions' => $versions,
                    'latest_version' => $versions[0]['version'] ?? null,
                ]
            ];

        } catch (Exception $e) {
            Log::error("Failed to get package info: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get package download URL for specific version
     */
    public function getPackageDownloadUrl(string $packageName, string $version): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'token ' . $this->token,
            ])->get("{$this->baseUrl}/repos/{$this->owner}/{$packageName}/releases/tags/v{$version}");

            if ($response->successful()) {
                $release = $response->json();
                return [
                    'success' => true,
                    'download_url' => $release['tarball_url'] ?? '',
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Version not found'
                ];
            }

        } catch (Exception $e) {
            Log::error("Failed to get download URL: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * List all packages
     */
    public function listPackages(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'token ' . $this->token,
            ])->get("{$this->baseUrl}/orgs/{$this->owner}/repos", [
                'type' => 'all',
                'sort' => 'updated',
                'direction' => 'desc',
            ]);

            if ($response->successful()) {
                $repos = $response->json();
                $packages = [];

                foreach ($repos as $repo) {
                    $packages[] = [
                        'name' => $repo['name'],
                        'description' => $repo['description'] ?? '',
                        'repository_url' => $repo['html_url'] ?? '',
                        'updated_at' => $repo['updated_at'] ?? '',
                    ];
                }

                return [
                    'success' => true,
                    'data' => $packages
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to list repositories'
                ];
            }

        } catch (Exception $e) {
            Log::error("Failed to list packages: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}