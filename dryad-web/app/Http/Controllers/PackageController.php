<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\PackageService;
use App\Models\Package; // Certifique-se de que o modelo está corretamente importado

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
        // Supondo que o modelo seja Package
        $packages = Package::paginate(20); // ou o número desejado por página

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