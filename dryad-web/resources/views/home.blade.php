@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Dryad Package Manager</h1>
                <p class="lead mb-4">Discover and share powerful packages for the Dryad programming language. Build better applications faster with our growing ecosystem of tools and libraries.</p>
                
                <!-- Search Box -->
                <div class="row">
                    <div class="col-md-10">
                        <form action="{{ route('search') }}" method="GET" class="d-flex">
                            <input type="text" name="q" class="form-control search-box me-2" placeholder="Search packages..." value="{{ request('q') }}">
                            <button type="submit" class="btn btn-light px-4">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <i class="bi bi-box-seam display-1 text-white"></i>
            </div>
        </div>
    </div>
</section>

<!-- Quick Start Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="mb-4">Get Started with Dryad</h2>
                <p class="mb-4">Install packages and start building amazing applications with the Dryad programming language.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 package-card">
                    <div class="card-body text-center">
                        <i class="bi bi-download display-4 text-primary mb-3"></i>
                        <h5 class="card-title">Install Oak CLI</h5>
                        <p class="card-text">Get the Oak command-line tool to manage your Dryad packages.</p>
                        <code class="bg-light p-2 rounded d-block">oak install package-name</code>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 package-card">
                    <div class="card-body text-center">
                        <i class="bi bi-search display-4 text-success mb-3"></i>
                        <h5 class="card-title">Discover Packages</h5>
                        <p class="card-text">Browse thousands of packages created by the Dryad community.</p>
                        <a href="{{ route('packages.index') }}" class="btn btn-outline-success">Browse Packages</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 package-card">
                    <div class="card-body text-center">
                        <i class="bi bi-upload display-4 text-info mb-3"></i>
                        <h5 class="card-title">Publish Your Own</h5>
                        <p class="card-text">Share your packages with the Dryad developer community.</p>
                        @auth
                            <a href="{{ route('developer.dashboard') }}" class="btn btn-outline-info">Developer Dashboard</a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-outline-info">Get Started</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Packages -->
<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2 class="mb-4">Featured Packages</h2>
                <p>Popular and trending packages from the Dryad community.</p>
            </div>
        </div>
        
        <div class="row">
            @forelse($featuredPackages ?? [] as $package)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card package-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $package['name'] }}</h5>
                            <p class="card-text text-muted small mb-2">v{{ $package['version'] ?? '1.0.0' }}</p>
                            <p class="card-text">{{ Str::limit($package['description'] ?? 'No description available.', 100) }}</p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-download me-1"></i>{{ number_format($package['downloads'] ?? 0) }}
                                </small>
                                <a href="{{ route('packages.show', $package['name']) }}" class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <div class="card">
                        <div class="card-body py-5">
                            <i class="bi bi-box display-4 text-muted mb-3"></i>
                            <h5 class="text-muted">No packages available yet</h5>
                            <p class="text-muted">Be the first to publish a package to the Dryad registry!</p>
                            @auth
                                <a href="{{ route('developer.dashboard') }}" class="btn btn-primary">Publish a Package</a>
                            @else
                                <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                            @endauth
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="card border-0">
                    <div class="card-body">
                        <i class="bi bi-box display-4 text-primary mb-2"></i>
                        <h3 class="fw-bold text-primary">{{ number_format($stats['total_packages'] ?? 0) }}</h3>
                        <p class="text-muted mb-0">Total Packages</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card border-0">
                    <div class="card-body">
                        <i class="bi bi-download display-4 text-success mb-2"></i>
                        <h3 class="fw-bold text-success">{{ number_format($stats['total_downloads'] ?? 0) }}</h3>
                        <p class="text-muted mb-0">Total Downloads</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card border-0">
                    <div class="card-body">
                        <i class="bi bi-people display-4 text-info mb-2"></i>
                        <h3 class="fw-bold text-info">{{ number_format($stats['total_users'] ?? 0) }}</h3>
                        <p class="text-muted mb-0">Developers</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card border-0">
                    <div class="card-body">
                        <i class="bi bi-calendar-week display-4 text-warning mb-2"></i>
                        <h3 class="fw-bold text-warning">{{ number_format($stats['weekly_downloads'] ?? 0) }}</h3>
                        <p class="text-muted mb-0">Weekly Downloads</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
