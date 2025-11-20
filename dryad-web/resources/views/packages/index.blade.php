@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="mb-3">Browse Packages</h1>
            <p class="text-muted">Discover packages for the Dryad programming language ecosystem.</p>
        </div>
        <div class="col-lg-4">
            <form action="{{ route('packages.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search packages..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Filters and Sorting -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="btn-group" role="group">
                <a href="{{ route('packages.index', array_merge(request()->query(), ['sort' => 'popularity'])) }}" 
                   class="btn {{ request('sort', 'popularity') == 'popularity' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Most Popular
                </a>
                <a href="{{ route('packages.index', array_merge(request()->query(), ['sort' => 'recent'])) }}" 
                   class="btn {{ request('sort') == 'recent' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Recently Updated
                </a>
                <a href="{{ route('packages.index', array_merge(request()->query(), ['sort' => 'name'])) }}" 
                   class="btn {{ request('sort') == 'name' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Name A-Z
                </a>
            </div>
        </div>
        <div class="col-md-6 text-end">
            <span class="text-muted">{{ number_format($packages->total() ?? count($packages ?? [])) }} packages found</span>
        </div>
    </div>

    <!-- Packages Grid -->
    <div class="row">
        @forelse($packages ?? [] as $package)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card package-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">
                                <a href="{{ route('packages.show', $package['name']) }}" class="text-decoration-none">
                                    {{ $package['name'] }}
                                </a>
                            </h5>
                            <span class="badge bg-secondary">v{{ $package['version'] ?? '1.0.0' }}</span>
                        </div>
                        
                        <p class="text-muted small mb-2">
                            by {{ $package['author'] ?? 'Unknown' }}
                        </p>
                        
                        <p class="card-text flex-grow-1">
                            {{ Str::limit($package['description'] ?? 'No description available.', 120) }}
                        </p>
                        
                        @if(!empty($package['keywords']))
                            <div class="mb-3">
                                @foreach(array_slice($package['keywords'], 0, 3) as $keyword)
                                    <span class="badge bg-light text-dark me-1">{{ $keyword }}</span>
                                @endforeach
                            </div>
                        @endif
                        
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <div class="text-muted small">
                                <i class="bi bi-download me-1"></i>
                                {{ number_format($package['downloads'] ?? 0) }} downloads
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-clock me-1"></i>
                                {{ isset($package['updated_at']) ? \Carbon\Carbon::parse($package['updated_at'])->diffForHumans() : 'Unknown' }}
                            </div>
                        </div>
                        
                        <div class="mt-3 d-flex justify-content-between">
                            <a href="{{ route('packages.show', $package['name']) }}" class="btn btn-outline-primary btn-sm">
                                View Details
                            </a>
                            <button class="btn btn-outline-secondary btn-sm" onclick="copyInstallCommand('{{ $package['name'] }}')">
                                <i class="bi bi-clipboard"></i> Copy Install
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-box display-1 text-muted mb-3"></i>
                    <h3 class="text-muted mb-3">No packages found</h3>
                    @if(request('search'))
                        <p class="text-muted mb-4">No packages match your search for "{{ request('search') }}".</p>
                        <a href="{{ route('packages.index') }}" class="btn btn-primary">Browse All Packages</a>
                    @else
                        <p class="text-muted mb-4">The registry is empty. Be the first to publish a package!</p>
                        @auth
                            <a href="{{ route('developer.dashboard') }}" class="btn btn-primary">Publish a Package</a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                        @endauth
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if(method_exists($packages ?? collect(), 'hasPages') && $packages->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $packages->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<script>
function copyInstallCommand(packageName) {
    const command = `oak install ${packageName}`;
    navigator.clipboard.writeText(command).then(() => {
        // Show a toast or alert
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-body">
                    Install command copied to clipboard!
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 3000);
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}
</script>
@endsection