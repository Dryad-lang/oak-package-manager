@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Search Header -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="mb-3">
                Search Results
                @if(request('q'))
                    <small class="text-muted">for "{{ request('q') }}"</small>
                @endif
            </h1>
            <p class="text-muted">
                {{ $results['total'] ?? 0 }} packages found
                @if(request('q'))
                    matching your search
                @endif
            </p>
        </div>
        <div class="col-lg-4">
            <form action="{{ route('search') }}" method="GET" class="d-flex">
                <input type="text" name="q" class="form-control me-2" 
                       placeholder="Search packages..." 
                       value="{{ request('q') }}" 
                       autofocus>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>

    @if(request('q'))
        <!-- Search Filters -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="btn-group" role="group">
                    <a href="{{ route('search', array_merge(request()->query(), ['sort' => 'relevance'])) }}" 
                       class="btn {{ request('sort', 'relevance') == 'relevance' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Most Relevant
                    </a>
                    <a href="{{ route('search', array_merge(request()->query(), ['sort' => 'popularity'])) }}" 
                       class="btn {{ request('sort') == 'popularity' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Most Popular
                    </a>
                    <a href="{{ route('search', array_merge(request()->query(), ['sort' => 'recent'])) }}" 
                       class="btn {{ request('sort') == 'recent' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Recently Updated
                    </a>
                </div>
            </div>
            <div class="col-md-4 text-end">
                @if(request('q'))
                    <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">
                        Clear Search
                    </a>
                @endif
            </div>
        </div>

        <!-- Search Results -->
        <div class="row">
            @forelse($results['packages'] ?? [] as $package)
                <div class="col-lg-6 mb-4">
                    <div class="card package-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    <a href="{{ route('packages.show', $package['name']) }}" class="text-decoration-none">
                                        {!! highlightSearchTerm($package['name'], request('q')) !!}
                                    </a>
                                </h5>
                                <span class="badge bg-secondary">v{{ $package['version'] ?? '1.0.0' }}</span>
                            </div>
                            
                            <p class="text-muted small mb-2">
                                by {{ $package['author'] ?? 'Unknown' }}
                            </p>
                            
                            <p class="card-text">
                                {!! highlightSearchTerm(Str::limit($package['description'] ?? 'No description available.', 150), request('q')) !!}
                            </p>
                            
                            @if(!empty($package['keywords']))
                                <div class="mb-3">
                                    @foreach(array_slice($package['keywords'], 0, 4) as $keyword)
                                        <span class="badge bg-light text-dark me-1">
                                            {!! highlightSearchTerm($keyword, request('q')) !!}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    <i class="bi bi-download me-1"></i>
                                    {{ number_format($package['downloads'] ?? 0) }} downloads
                                </div>
                                <div class="text-muted small">
                                    {{ isset($package['updated_at']) ? \Carbon\Carbon::parse($package['updated_at'])->diffForHumans() : 'Unknown' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-search display-1 text-muted mb-3"></i>
                        <h3 class="text-muted mb-3">No packages found</h3>
                        <p class="text-muted mb-4">
                            We couldn't find any packages matching "{{ request('q') }}".
                        </p>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <h6>Try:</h6>
                                <ul class="list-unstyled text-start">
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Checking your spelling</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Using broader search terms</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Searching for related keywords</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Browsing popular packages</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('packages.index') }}" class="btn btn-primary me-2">Browse All Packages</a>
                            <button class="btn btn-outline-secondary" onclick="document.querySelector('input[name=q]').focus()">
                                Try Another Search
                            </button>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if(isset($results['packages']) && method_exists($results['packages'], 'hasPages') && $results['packages']->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $results['packages']->appends(request()->query())->links() }}
            </div>
        @endif

        <!-- Search Suggestions -->
        @if(!empty($suggestions))
            <div class="mt-5">
                <h5>Did you mean?</h5>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($suggestions as $suggestion)
                        <a href="{{ route('search', ['q' => $suggestion]) }}" 
                           class="btn btn-outline-primary btn-sm">
                            {{ $suggestion }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Popular Searches -->
        @if(!empty($popularSearches))
            <div class="mt-5">
                <h5>Popular Searches</h5>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($popularSearches as $search)
                        <a href="{{ route('search', ['q' => $search]) }}" 
                           class="btn btn-outline-secondary btn-sm">
                            {{ $search }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    @else
        <!-- Search Landing Page -->
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <i class="bi bi-search display-1 text-muted mb-4"></i>
                <h2 class="mb-4">Search Dryad Packages</h2>
                <p class="lead text-muted mb-5">
                    Find the perfect packages for your Dryad projects. Search by name, keywords, or description.
                </p>
            </div>
        </div>

        <!-- Popular Categories -->
        <div class="row mb-5">
            <div class="col-12">
                <h4 class="mb-4">Popular Categories</h4>
                <div class="row">
                    @foreach([
                        ['name' => 'Web Development', 'icon' => 'globe', 'keywords' => ['web', 'http', 'server']],
                        ['name' => 'Utilities', 'icon' => 'tools', 'keywords' => ['util', 'helper', 'tool']],
                        ['name' => 'Data Processing', 'icon' => 'database', 'keywords' => ['data', 'json', 'csv']],
                        ['name' => 'CLI Tools', 'icon' => 'terminal', 'keywords' => ['cli', 'command', 'terminal']],
                        ['name' => 'Testing', 'icon' => 'check-circle', 'keywords' => ['test', 'testing', 'spec']],
                        ['name' => 'Math & Science', 'icon' => 'calculator', 'keywords' => ['math', 'science', 'algorithm']]
                    ] as $category)
                        <div class="col-md-4 col-lg-2 mb-3">
                            <a href="{{ route('search', ['q' => $category['keywords'][0]]) }}" 
                               class="card text-decoration-none h-100 category-card">
                                <div class="card-body text-center">
                                    <i class="bi bi-{{ $category['icon'] }} display-6 text-primary mb-2"></i>
                                    <h6 class="card-title">{{ $category['name'] }}</h6>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Search Tips -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Search Tips</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-lightbulb text-warning me-2"></i>Search by Keywords</h6>
                                <p class="small text-muted">Use relevant keywords like "web", "cli", "json" to find packages.</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-person text-info me-2"></i>Find by Author</h6>
                                <p class="small text-muted">Search for packages by a specific developer or organization.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.category-card {
    transition: transform 0.2s;
    border: 1px solid #e0e0e0;
}

.category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    text-decoration: none;
}

.highlight {
    background-color: #fff3cd;
    font-weight: bold;
}
</style>

@php
function highlightSearchTerm($text, $term) {
    if (empty($term)) {
        return $text;
    }
    
    return preg_replace('/(' . preg_quote($term, '/') . ')/i', '<span class="highlight">$1</span>', $text);
}
@endphp
@endsection