@extends('layouts.app')

@section('content')
<div class="container py-4">
    @if(isset($package))
        <!-- Package Header -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('packages.index') }}">Packages</a></li>
                        <li class="breadcrumb-item active">{{ $package['name'] }}</li>
                    </ol>
                </nav>
                
                <div class="d-flex align-items-center mb-3">
                    <h1 class="me-3">{{ $package['name'] }}</h1>
                    <span class="badge bg-primary fs-6">v{{ $package['version'] ?? '1.0.0' }}</span>
                </div>
                
                <p class="lead text-muted mb-3">{{ $package['description'] ?? 'No description available.' }}</p>
                
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <span class="text-muted">
                        <i class="bi bi-person me-1"></i>
                        by <strong>{{ $package['author'] ?? 'Unknown' }}</strong>
                    </span>
                    <span class="text-muted">
                        <i class="bi bi-download me-1"></i>
                        <strong>{{ number_format($package['downloads'] ?? 0) }}</strong> downloads
                    </span>
                    <span class="text-muted">
                        <i class="bi bi-clock me-1"></i>
                        Last updated {{ isset($package['updated_at']) ? \Carbon\Carbon::parse($package['updated_at'])->diffForHumans() : 'unknown' }}
                    </span>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Installation</h5>
                    </div>
                    <div class="card-body">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="installCommand" value="oak install {{ $package['name'] }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyInstallCommand()">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        
                        @if(!empty($package['license']))
                            <div class="mb-2">
                                <strong>License:</strong> {{ $package['license'] }}
                            </div>
                        @endif
                        
                        @if(!empty($package['homepage']))
                            <div class="mb-2">
                                <a href="{{ $package['homepage'] }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-house me-1"></i>Homepage
                                </a>
                            </div>
                        @endif
                        
                        @if(!empty($package['repository']))
                            <div class="mb-2">
                                <a href="{{ $package['repository'] }}" target="_blank" class="btn btn-outline-dark btn-sm">
                                    <i class="bi bi-github me-1"></i>Repository
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Package Content -->
        <div class="row">
            <div class="col-lg-8">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" id="packageTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="readme-tab" data-bs-toggle="tab" data-bs-target="#readme" type="button">
                            <i class="bi bi-file-text me-1"></i>README
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="versions-tab" data-bs-toggle="tab" data-bs-target="#versions" type="button">
                            <i class="bi bi-clock-history me-1"></i>Versions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="dependencies-tab" data-bs-toggle="tab" data-bs-target="#dependencies" type="button">
                            <i class="bi bi-diagram-3 me-1"></i>Dependencies
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="packageTabsContent">
                    <!-- README Tab -->
                    <div class="tab-pane fade show active" id="readme" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                @if(!empty($package['readme']))
                                    <div class="markdown-content">
                                        {!! nl2br(e($package['readme'])) !!}
                                    </div>
                                @else
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-file-text display-4 mb-3"></i>
                                        <p>No README file available for this package.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Versions Tab -->
                    <div class="tab-pane fade" id="versions" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                @if(!empty($package['versions']))
                                    <div class="list-group">
                                        @foreach($package['versions'] as $version)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">v{{ $version['version'] }}</h6>
                                                    <small class="text-muted">
                                                        Released {{ isset($version['published_at']) ? \Carbon\Carbon::parse($version['published_at'])->diffForHumans() : 'unknown' }}
                                                    </small>
                                                </div>
                                                @if($version['version'] == $package['version'])
                                                    <span class="badge bg-success">Latest</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-clock-history display-4 mb-3"></i>
                                        <p>No version history available.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Dependencies Tab -->
                    <div class="tab-pane fade" id="dependencies" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                @if(!empty($package['dependencies']))
                                    <h6>Runtime Dependencies</h6>
                                    <div class="list-group mb-4">
                                        @foreach($package['dependencies'] as $dep => $version)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <a href="{{ route('packages.show', $dep) }}" class="text-decoration-none">{{ $dep }}</a>
                                                <code>{{ $version }}</code>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                
                                @if(!empty($package['devDependencies']))
                                    <h6>Development Dependencies</h6>
                                    <div class="list-group">
                                        @foreach($package['devDependencies'] as $dep => $version)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <a href="{{ route('packages.show', $dep) }}" class="text-decoration-none">{{ $dep }}</a>
                                                <code>{{ $version }}</code>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                
                                @if(empty($package['dependencies']) && empty($package['devDependencies']))
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-diagram-3 display-4 mb-3"></i>
                                        <p>This package has no dependencies.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Keywords -->
                @if(!empty($package['keywords']))
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Keywords</h6>
                        </div>
                        <div class="card-body">
                            @foreach($package['keywords'] as $keyword)
                                <a href="{{ route('packages.index', ['search' => $keyword]) }}" class="badge bg-light text-dark me-1 mb-1 text-decoration-none">
                                    {{ $keyword }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Statistics -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="h4 text-primary mb-1">{{ number_format($package['downloads'] ?? 0) }}</div>
                                <small class="text-muted">Downloads</small>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="h4 text-success mb-1">{{ number_format($package['weeklyDownloads'] ?? 0) }}</div>
                                <small class="text-muted">Weekly</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Packages -->
                @if(!empty($relatedPackages))
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Related Packages</h6>
                        </div>
                        <div class="card-body">
                            @foreach($relatedPackages as $related)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <a href="{{ route('packages.show', $related['name']) }}" class="text-decoration-none">
                                        {{ $related['name'] }}
                                    </a>
                                    <small class="text-muted">{{ number_format($related['downloads'] ?? 0) }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Package Not Found -->
        <div class="text-center py-5">
            <i class="bi bi-exclamation-triangle display-1 text-warning mb-3"></i>
            <h2>Package Not Found</h2>
            <p class="text-muted mb-4">The package you're looking for doesn't exist or has been removed.</p>
            <a href="{{ route('packages.index') }}" class="btn btn-primary">Browse All Packages</a>
        </div>
    @endif
</div>

<script>
function copyInstallCommand() {
    const commandInput = document.getElementById('installCommand');
    commandInput.select();
    commandInput.setSelectionRange(0, 99999);
    
    navigator.clipboard.writeText(commandInput.value).then(() => {
        // Show success feedback
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check"></i>';
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalContent;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}
</script>

<style>
.markdown-content {
    line-height: 1.6;
}

.markdown-content h1,
.markdown-content h2,
.markdown-content h3 {
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
}

.markdown-content code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
}

.markdown-content pre {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    overflow-x: auto;
}
</style>
@endsection