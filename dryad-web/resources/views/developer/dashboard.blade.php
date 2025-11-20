@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="mb-3">Developer Dashboard</h1>
            <p class="text-muted">Manage your packages and monitor their performance.</p>
        </div>
        <div class="col-lg-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#publishModal">
                <i class="bi bi-plus-circle me-2"></i>Publish Package
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-box display-4 text-primary mb-2"></i>
                    <h3 class="text-primary">{{ $stats['total_packages'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">Your Packages</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-download display-4 text-success mb-2"></i>
                    <h3 class="text-success">{{ number_format($stats['total_downloads'] ?? 0) }}</h3>
                    <p class="text-muted mb-0">Total Downloads</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-calendar-week display-4 text-info mb-2"></i>
                    <h3 class="text-info">{{ number_format($stats['weekly_downloads'] ?? 0) }}</h3>
                    <p class="text-muted mb-0">Weekly Downloads</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-star display-4 text-warning mb-2"></i>
                    <h3 class="text-warning">{{ $stats['avg_rating'] ?? '0.0' }}</h3>
                    <p class="text-muted mb-0">Avg Rating</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Your Packages -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Your Packages</h5>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary active" data-filter="all">All</button>
                <button class="btn btn-outline-secondary" data-filter="published">Published</button>
                <button class="btn btn-outline-secondary" data-filter="draft">Draft</button>
            </div>
        </div>
        <div class="card-body">
            @forelse($packages ?? [] as $package)
                <div class="row align-items-center border-bottom py-3 package-row" data-status="{{ $package['status'] ?? 'published' }}">
                    <div class="col-md-6">
                        <h6 class="mb-1">
                            <a href="{{ route('packages.show', $package['name']) }}" class="text-decoration-none">
                                {{ $package['name'] }}
                            </a>
                            @if($package['status'] === 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </h6>
                        <p class="text-muted small mb-1">{{ Str::limit($package['description'] ?? 'No description', 80) }}</p>
                        <small class="text-muted">
                            Version {{ $package['version'] ?? '1.0.0' }} • 
                            Updated {{ isset($package['updated_at']) ? \Carbon\Carbon::parse($package['updated_at'])->diffForHumans() : 'unknown' }}
                        </small>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="row">
                            <div class="col-6">
                                <div class="small text-muted">Downloads</div>
                                <div class="fw-bold">{{ number_format($package['downloads'] ?? 0) }}</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Weekly</div>
                                <div class="fw-bold text-success">{{ number_format($package['weekly_downloads'] ?? 0) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('packages.show', $package['name']) }}">View Package</a></li>
                                <li><a class="dropdown-item" href="#" onclick="editPackage('{{ $package['name'] }}')">Edit Details</a></li>
                                <li><a class="dropdown-item" href="#" onclick="publishVersion('{{ $package['name'] }}')">Publish Version</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deletePackage('{{ $package['name'] }}')">Delete Package</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-box display-1 text-muted mb-3"></i>
                    <h5 class="text-muted">No packages yet</h5>
                    <p class="text-muted mb-4">Get started by publishing your first package to the Dryad registry.</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#publishModal">
                        <i class="bi bi-plus-circle me-2"></i>Publish Your First Package
                    </button>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Publish Package Modal -->
<div class="modal fade" id="publishModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Publish Package</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="publishForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Package Name *</label>
                            <input type="text" class="form-control" name="name" required 
                                   placeholder="my-awesome-package" pattern="[a-z0-9-]+">
                            <div class="form-text">Lowercase letters, numbers, and hyphens only</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Version *</label>
                            <input type="text" class="form-control" name="version" required 
                                   placeholder="1.0.0" pattern="^\d+\.\d+\.\d+$">
                            <div class="form-text">Semantic versioning (e.g., 1.0.0)</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="3" required
                                  placeholder="A brief description of what your package does..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Author</label>
                            <input type="text" class="form-control" name="author" value="{{ auth()->user()->name ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">License</label>
                            <select class="form-select" name="license">
                                <option value="">Select License</option>
                                <option value="MIT">MIT</option>
                                <option value="Apache-2.0">Apache 2.0</option>
                                <option value="GPL-3.0">GPL 3.0</option>
                                <option value="BSD-3-Clause">BSD 3-Clause</option>
                                <option value="Unlicense">Unlicense</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Homepage URL</label>
                            <input type="url" class="form-control" name="homepage" placeholder="https://...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Repository URL</label>
                            <input type="url" class="form-control" name="repository" placeholder="https://github.com/...">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Keywords</label>
                        <input type="text" class="form-control" name="keywords" 
                               placeholder="dryad, library, utility" 
                               data-bs-toggle="tooltip" 
                               title="Comma-separated keywords to help users find your package">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Package Archive *</label>
                        <input type="file" class="form-control" name="archive" accept=".tar.gz,.zip" required>
                        <div class="form-text">Upload a .tar.gz or .zip file containing your package</div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="publish_as_draft" id="publishAsDraft">
                        <label class="form-check-label" for="publishAsDraft">
                            Publish as draft (not publicly visible)
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="publishForm" class="btn btn-primary">
                    <i class="bi bi-upload me-1"></i>Publish Package
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Filter packages
document.querySelectorAll('[data-filter]').forEach(button => {
    button.addEventListener('click', function() {
        const filter = this.dataset.filter;
        
        // Update active button
        document.querySelectorAll('[data-filter]').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        
        // Filter package rows
        document.querySelectorAll('.package-row').forEach(row => {
            const status = row.dataset.status;
            if (filter === 'all' || status === filter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

// Handle form submission
document.getElementById('publishForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.querySelector('button[type="submit"][form="publishForm"]');
    
    // Show loading state
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Publishing...';
    submitBtn.disabled = true;
    
    // Simulate API call (replace with actual implementation)
    setTimeout(() => {
        alert('Package published successfully! (This is a demo - implement actual API call)');
        
        // Reset form and modal
        this.reset();
        submitBtn.innerHTML = '<i class="bi bi-upload me-1"></i>Publish Package';
        submitBtn.disabled = false;
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('publishModal'));
        modal.hide();
        
        // Reload page to show new package
        location.reload();
    }, 2000);
});

function editPackage(packageName) {
    alert(`Edit package: ${packageName} (To be implemented)`);
}

function publishVersion(packageName) {
    alert(`Publish new version for: ${packageName} (To be implemented)`);
}

function deletePackage(packageName) {
    if (confirm(`Are you sure you want to delete "${packageName}"? This action cannot be undone.`)) {
        alert(`Delete package: ${packageName} (To be implemented)`);
    }
}

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
@endsection