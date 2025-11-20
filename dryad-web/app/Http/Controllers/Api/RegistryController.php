<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ForgejoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class RegistryController extends Controller
{
    protected ForgejoService $forgejoService;

    public function __construct(ForgejoService $forgejoService)
    {
        $this->forgejoService = $forgejoService;
    }

    /**
     * Publish a new package version
     */
    public function publish(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|regex:/^[a-z0-9_-]+$/',
                'version' => 'required|string|regex:/^\d+\.\d+\.\d+$/',
                'description' => 'string|nullable',
                'author' => 'string|nullable',
                'license' => 'string|nullable',
                'homepage' => 'url|nullable',
                'repository' => 'url|nullable',
                'keywords' => 'array|nullable',
                'dependencies' => 'array|nullable',
                'files' => 'required|array',
                'files.*.path' => 'required|string',
                'files.*.content' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'details' => $validator->errors()
                ], 400);
            }

            $data = $validator->validated();
            
            Log::info("Publishing package: {$data['name']} version {$data['version']}");

            // Create or update repository in Forgejo
            $repoResult = $this->forgejoService->createOrUpdateRepository(
                $data['name'],
                $data['description'] ?? "Dryad package: {$data['name']}"
            );

            if (!$repoResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to create repository',
                    'details' => $repoResult['error']
                ], 500);
            }

            // Create version tag and upload files
            $versionResult = $this->forgejoService->createVersion(
                $data['name'],
                $data['version'],
                $data['files'],
                [
                    'description' => $data['description'] ?? '',
                    'author' => $data['author'] ?? '',
                    'license' => $data['license'] ?? '',
                    'homepage' => $data['homepage'] ?? '',
                    'repository' => $data['repository'] ?? '',
                    'keywords' => $data['keywords'] ?? [],
                    'dependencies' => $data['dependencies'] ?? [],
                ]
            );

            if (!$versionResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to create version',
                    'details' => $versionResult['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => "Package {$data['name']} version {$data['version']} published successfully",
                'data' => [
                    'name' => $data['name'],
                    'version' => $data['version'],
                    'repository_url' => $repoResult['repository_url'],
                    'download_url' => $versionResult['download_url']
                ]
            ], 201);

        } catch (Exception $e) {
            Log::error("Registry publish error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'details' => config('app.debug') ? $e->getMessage() : 'Please check server logs'
            ], 500);
        }
    }

    /**
     * Get package information
     */
    public function getPackage(string $packageName): JsonResponse
    {
        try {
            Log::info("Fetching package information: {$packageName}");

            $packageInfo = $this->forgejoService->getPackageInfo($packageName);

            if (!$packageInfo['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Package not found',
                    'details' => $packageInfo['error']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $packageInfo['data']
            ]);

        } catch (Exception $e) {
            Log::error("Registry get package error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'details' => config('app.debug') ? $e->getMessage() : 'Please check server logs'
            ], 500);
        }
    }

    /**
     * Download package version
     */
    public function downloadPackage(string $packageName, string $version): JsonResponse
    {
        try {
            Log::info("Downloading package: {$packageName} version {$version}");

            $downloadResult = $this->forgejoService->getPackageDownloadUrl($packageName, $version);

            if (!$downloadResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Package version not found',
                    'details' => $downloadResult['error']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'download_url' => $downloadResult['download_url'],
                    'package' => $packageName,
                    'version' => $version
                ]
            ]);

        } catch (Exception $e) {
            Log::error("Registry download error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'details' => config('app.debug') ? $e->getMessage() : 'Please check server logs'
            ], 500);
        }
    }

    /**
     * List all packages
     */
    public function listPackages(): JsonResponse
    {
        try {
            Log::info("Listing all packages");

            $packagesResult = $this->forgejoService->listPackages();

            if (!$packagesResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to list packages',
                    'details' => $packagesResult['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $packagesResult['data']
            ]);

        } catch (Exception $e) {
            Log::error("Registry list packages error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'details' => config('app.debug') ? $e->getMessage() : 'Please check server logs'
            ], 500);
        }
    }
}