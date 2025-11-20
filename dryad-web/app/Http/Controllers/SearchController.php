<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PackageService;

class SearchController extends Controller
{
    protected $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Handle search requests.
     */
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $sort = $request->get('sort', 'relevance');
        
        if (empty($query)) {
            return view('search', [
                'results' => ['packages' => [], 'total' => 0],
                'suggestions' => $this->getPopularSearches(),
                'popularSearches' => $this->getPopularSearches()
            ]);
        }

        // Get search results from registry API
        $results = $this->packageService->searchPackages($query, $sort, 20);

        // Simulando busca (em produção usaria API de busca)
        $allPackages = [
            [
                'name' => 'matematica-utils',
                'version' => '1.1.0',
                'description' => 'Biblioteca completa de utilitários matemáticos para Dryad incluindo álgebra, geometria e estatística',
                'downloads' => 1250,
                'author' => 'Dryad Community',
                'keywords' => ['matematica', 'algebra', 'geometria', 'estatistica'],
                'updated_at' => '2025-11-18T10:30:00Z'
            ],
            [
                'name' => 'dryad-stdlib',
                'version' => '0.1.0',
                'description' => 'Biblioteca padrão oficial do Dryad com funções essenciais',
                'downloads' => 2340,
                'author' => 'Dryad Core Team',
                'keywords' => ['stdlib', 'core', 'essencial'],
                'updated_at' => '2025-11-15T14:20:00Z'
            ],
            [
                'name' => 'file-utils',
                'version' => '2.0.0',
                'description' => 'Utilitários para manipulação de arquivos e diretórios',
                'downloads' => 890,
                'author' => 'Community',
                'keywords' => ['file', 'directory', 'utils'],
                'updated_at' => '2025-11-10T16:45:00Z'
            ],
            [
                'name' => 'http-client',
                'version' => '1.2.3',
                'description' => 'Cliente HTTP simples e poderoso para requisições web',
                'downloads' => 1890,
                'author' => 'WebDev Team',
                'keywords' => ['http', 'client', 'web', 'requisicoes'],
                'updated_at' => '2025-11-12T09:15:00Z'
            ],
            [
                'name' => 'json-parser',
                'version' => '0.8.2',
                'description' => 'Parser JSON rápido e eficiente para Dryad',
                'downloads' => 1567,
                'author' => 'JSON Guild',
                'keywords' => ['json', 'parser', 'data'],
                'updated_at' => '2025-11-08T11:30:00Z'
            ]
        ];

        // Busca nos pacotes
        $searchLower = strtolower($query);
        $results = array_filter($allPackages, function($package) use ($searchLower) {
            // Buscar no nome
            if (str_contains(strtolower($package['name']), $searchLower)) {
                return true;
            }
            
            // Buscar na descrição
            if (str_contains(strtolower($package['description']), $searchLower)) {
                return true;
            }
            
            // Buscar nas keywords
            if (isset($package['keywords'])) {
                foreach ($package['keywords'] as $keyword) {
                    if (str_contains(strtolower($keyword), $searchLower)) {
                        return true;
                    }
                }
            }
            
            // Buscar no autor
            if (str_contains(strtolower($package['author']), $searchLower)) {
                return true;
            }
            
            return false;
        });

        // Ordenar por relevância (packages com match no nome primeiro)
        usort($results, function($a, $b) use ($searchLower) {
            $aNameMatch = str_contains(strtolower($a['name']), $searchLower);
            $bNameMatch = str_contains(strtolower($b['name']), $searchLower);
            
            if ($aNameMatch && !$bNameMatch) return -1;
            if (!$aNameMatch && $bNameMatch) return 1;
            
            // Se ambos ou nenhum tem match no nome, ordenar por downloads
            return $b['downloads'] - $a['downloads'];
        });

        $results = collect($results);
        $totalResults = $results->count();

        return view('search', compact('results'));
    }

    /**
     * Get popular search terms
     */
    private function getPopularSearches()
    {
        return ['math', 'http', 'file', 'utils', 'stdlib', 'crypto'];
    }
}