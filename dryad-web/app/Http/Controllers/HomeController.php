<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PackageService;

class HomeController extends Controller
{
    protected $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get featured packages and stats from registry
        $featuredPackages = $this->packageService->getFeaturedPackages(6);
        $stats = $this->packageService->getStats();
        
        return view('home', compact('featuredPackages', 'stats'));
    }
}
