<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\PackageService;

class PackageController extends Controller
{
    protected $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Display a listing of packages.
     */
    public function index(Request $request)
    {
        // Simulando dados de pacotes (em produção viria de uma API)
        $allPackages = [
            [
                'name' => 'matematica-utils',
                'version' => '1.1.0',
                'description' => 'Biblioteca completa de utilitários matemáticos para Dryad incluindo álgebra, geometria e estatística',
                'downloads' => 1250,
                'author' => 'Dryad Community',
                'keywords' => ['matematica', 'algebra', 'geometria'],
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
                'keywords' => ['http', 'client', 'web'],
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
            ],
            [
                'name' => 'crypto-utils',
                'version' => '1.0.5',
                'description' => 'Funções criptográficas e de hashing para segurança',
                'downloads' => 756,
                'author' => 'Security Team',
                'keywords' => ['crypto', 'security', 'hash'],
                'updated_at' => '2025-11-14T13:22:00Z'
            ]
        ];

        // Filtrar por busca se fornecida
        $packages = $allPackages;
        if ($request->has('search') && !empty($request->search)) {
            $search = strtolower($request->search);
            $packages = array_filter($packages, function($package) use ($search) {
                return str_contains(strtolower($package['name']), $search) ||
                       str_contains(strtolower($package['description']), $search) ||
                       (isset($package['keywords']) && 
                        array_filter($package['keywords'], fn($k) => str_contains(strtolower($k), $search)));
            });
        }

        // Ordenar
        $sort = $request->get('sort', 'popularity');
        switch ($sort) {
            case 'recent':
                usort($packages, fn($a, $b) => strtotime($b['updated_at']) - strtotime($a['updated_at']));
                break;
            case 'name':
                usort($packages, fn($a, $b) => strcmp($a['name'], $b['name']));
                break;
            case 'popularity':
            default:
                usort($packages, fn($a, $b) => $b['downloads'] - $a['downloads']);
                break;
        }

        // Simular paginação
        $packages = collect($packages);

        return view('packages.index', compact('packages'));
    }

    /**
     * Display the specified package.
     */
    public function show(string $package)
    {
        // Simulando dados detalhados do pacote (em produção viria de uma API)
        $packageData = [
            'matematica-utils' => [
                'name' => 'matematica-utils',
                'version' => '1.1.0',
                'description' => 'Biblioteca completa de utilitários matemáticos para Dryad incluindo álgebra, geometria e estatística',
                'long_description' => 'Esta biblioteca oferece uma ampla gama de funções matemáticas para o desenvolvimento de aplicações científicas e educacionais em Dryad. Inclui módulos para álgebra linear, geometria euclidiana, estatística descritiva e inferencial, além de constantes matemáticas importantes.',
                'downloads' => 1250,
                'author' => 'Dryad Community',
                'license' => 'MIT',
                'homepage' => 'https://github.com/dryad-lang/matematica-utils',
                'repository' => 'https://github.com/dryad-lang/matematica-utils.git',
                'keywords' => ['matematica', 'algebra', 'geometria', 'estatistica', 'utils'],
                'dependencies' => [
                    'dryad-stdlib' => '^0.1.0'
                ],
                'dev_dependencies' => [
                    'dryad-test' => '^1.0.0'
                ],
                'versions' => [
                    '1.1.0' => '2025-11-18T10:30:00Z',
                    '1.0.2' => '2025-10-15T14:20:00Z',
                    '1.0.1' => '2025-09-28T09:15:00Z',
                    '1.0.0' => '2025-09-01T16:45:00Z'
                ],
                'readme' => "# Matematica Utils\n\nBiblioteca matemática completa para Dryad.\n\n## Instalação\n\n```bash\noak install matematica-utils\n```\n\n## Uso\n\n```dryad\nimport algebra from 'matematica-utils/algebra'\nimport geometria from 'matematica-utils/geometria'\n\n// Álgebra linear\nlet matriz = algebra.criarMatriz(3, 3)\nlet determinante = algebra.determinante(matriz)\n\n// Geometria\nlet area = geometria.areaCirculo(5.0)\nlet volume = geometria.volumeEsfera(3.0)\n```",
                'updated_at' => '2025-11-18T10:30:00Z',
                'created_at' => '2025-09-01T16:45:00Z'
            ]
        ];

        $packageInfo = $packageData[$package] ?? null;
        
        if (!$packageInfo) {
            abort(404, 'Package not found');
        }

        return view('packages.show', compact('packageInfo'));
    }
}