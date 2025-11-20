<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Dryad Package Manager') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .navbar-brand {
            font-weight: 600;
            color: #2d7d32 !important;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #2d7d32 0%, #4caf50 100%);
            color: white;
            padding: 4rem 0;
        }
        
        .search-box {
            border-radius: 25px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
        }
        
        .search-box:focus {
            border-color: #4caf50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        
        .btn-primary {
            background-color: #2d7d32;
            border-color: #2d7d32;
        }
        
        .btn-primary:hover {
            background-color: #1b5e20;
            border-color: #1b5e20;
        }
        
        .package-card {
            transition: transform 0.2s;
            border: 1px solid #e0e0e0;
        }
        
        .package-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .stats-section {
            background-color: #f8f9fa;
            padding: 3rem 0;
        }
        
        .footer {
            background-color: #2d7d32;
            color: white;
            padding: 2rem 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-tree-fill me-2"></i>Dryad Package Manager
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('packages.index') }}">Packages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('developer.dashboard') }}">Developer</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('developer.dashboard') }}">Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Dryad Package Manager</h5>
                    <p>The official package manager for the Dryad programming language.</p>
                </div>
                <div class="col-md-3">
                    <h6>Resources</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50">Documentation</a></li>
                        <li><a href="#" class="text-white-50">GitHub</a></li>
                        <li><a href="#" class="text-white-50">Community</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50">Help</a></li>
                        <li><a href="#" class="text-white-50">Contact</a></li>
                        <li><a href="#" class="text-white-50">Status</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; {{ date('Y') }} Dryad Package Manager. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
