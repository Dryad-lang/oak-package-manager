<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeveloperController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the developer dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Simulando dados do desenvolvedor (em produção viria de uma API)
        $userPackages = [
            [
                'name' => 'meu-pacote-util',
                'version' => '1.0.2',
                'description' => 'Um pacote útil criado por mim',
                'downloads' => 45,
                'status' => 'published',
                'updated_at' => '2025-11-15T10:30:00Z',
                'created_at' => '2025-10-01T14:20:00Z'
            ],
            [
                'name' => 'beta-package',
                'version' => '0.1.0-beta',
                'description' => 'Pacote em desenvolvimento',
                'downloads' => 12,
                'status' => 'beta',
                'updated_at' => '2025-11-18T16:45:00Z',
                'created_at' => '2025-11-16T09:15:00Z'
            ]
        ];

        $stats = [
            'total_packages' => count($userPackages),
            'total_downloads' => array_sum(array_column($userPackages, 'downloads')),
            'published_packages' => count(array_filter($userPackages, fn($p) => $p['status'] === 'published')),
            'beta_packages' => count(array_filter($userPackages, fn($p) => $p['status'] === 'beta'))
        ];

        return view('developer.dashboard', compact('userPackages', 'stats'));
    }

    /**
     * Show form to publish new package.
     */
    public function create()
    {
        return view('developer.publish');
    }

    /**
     * Handle package publication.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'version' => 'required|string|max:50',
            'description' => 'required|string|max:500',
            'package_file' => 'required|file|mimes:gz,tar|max:10240', // 10MB max
            'readme' => 'nullable|string',
            'homepage' => 'nullable|url',
            'repository' => 'nullable|url',
            'keywords' => 'nullable|string'
        ]);

        // Em produção, aqui enviaria para a API do registry
        // Por agora, apenas simular o sucesso
        
        return redirect()->route('developer.dashboard')
                        ->with('success', 'Package "' . $request->name . '" published successfully!');
    }

    /**
     * Show package details for editing.
     */
    public function show(string $package)
    {
        // Simulando dados do pacote do usuário
        $packageData = [
            'name' => $package,
            'version' => '1.0.2',
            'description' => 'Descrição do pacote',
            'readme' => '# Meu Pacote\n\nDescrição detalhada...',
            'homepage' => 'https://github.com/user/package',
            'repository' => 'https://github.com/user/package.git',
            'keywords' => 'util, helper, tools',
            'downloads' => 45,
            'status' => 'published',
            'versions' => [
                '1.0.2' => '2025-11-15T10:30:00Z',
                '1.0.1' => '2025-11-01T14:20:00Z',
                '1.0.0' => '2025-10-01T16:45:00Z'
            ]
        ];

        return view('developer.show', compact('packageData'));
    }

    /**
     * Show form to edit package.
     */
    public function edit(string $package)
    {
        // Simulando dados do pacote para edição
        $packageData = [
            'name' => $package,
            'version' => '1.0.2',
            'description' => 'Descrição do pacote',
            'readme' => '# Meu Pacote\n\nDescrição detalhada...',
            'homepage' => 'https://github.com/user/package',
            'repository' => 'https://github.com/user/package.git',
            'keywords' => 'util, helper, tools'
        ];

        return view('developer.edit', compact('packageData'));
    }

    /**
     * Update package information.
     */
    public function update(Request $request, string $package)
    {
        $request->validate([
            'description' => 'required|string|max:500',
            'readme' => 'nullable|string',
            'homepage' => 'nullable|url',
            'repository' => 'nullable|url',
            'keywords' => 'nullable|string'
        ]);

        // Em produção, enviaria para a API do registry
        
        return redirect()->route('developer.show', $package)
                        ->with('success', 'Package updated successfully!');
    }

    /**
     * Delete package.
     */
    public function destroy(string $package)
    {
        // Em produção, enviaria request de deleção para a API
        
        return redirect()->route('developer.dashboard')
                        ->with('success', 'Package "' . $package . '" deleted successfully!');
    }
}