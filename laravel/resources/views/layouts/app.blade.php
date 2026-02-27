<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EasyColoc') }} - Gestion de Colocation</title>

        <!-- Fonts & Icons -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Custom Premium CSS -->
        <link rel="stylesheet" href="{{ asset('css/premium.css') }}">
        
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="mb-10 px-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-home-user fa-lg"></i>
                    </div>
                    <span class="text-2xl font-bold tracking-tight text-gray-900 nav-label" style="font-family: 'Outfit', sans-serif;">EasyColoc</span>
                </a>
            </div>

            <nav>
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie w-6"></i>
                    <span class="nav-label">Dashboard</span>
                </a>
                
                @php
                    $activeColoc = auth()->user()->colocations()->wherePivotNull('left_at')->first();
                @endphp

                @if($activeColoc)
                    <a href="{{ route('colocations.show', $activeColoc) }}" class="nav-item {{ request()->routeIs('colocations.show') ? 'active' : '' }}">
                        <i class="fas fa-users w-6"></i>
                        <span class="nav-label">Ma Colocation</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-receipt w-6"></i>
                        <span class="nav-label">Dépenses</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-hand-holding-dollar w-6"></i>
                        <span class="nav-label">Remboursements</span>
                    </a>
                @else
                    <a href="{{ route('colocations.create') }}" class="nav-item {{ request()->routeIs('colocations.create') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle w-6"></i>
                        <span class="nav-label">Nouvelle Coloc</span>
                    </a>
                @endif

                <div class="my-6 border-t border-gray-100"></div>

                <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                    <i class="fas fa-user-circle w-6"></i>
                    <span class="nav-label">Mon Profil</span>
                </a>

                @if(auth()->user()->isGlobalAdmin())
                    <a href="{{ route('admin.index') }}" class="nav-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        <i class="fas fa-shield-halved w-6" style="color:#ef4444;"></i>
                        <span class="nav-label" style="color:#ef4444;">Admin Panel</span>
                    </a>
                @endif
            </nav>

            <div class="mt-auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item text-left border-none bg-transparent cursor-pointer hover:bg-red-50 hover:text-red-600" style="color: #ef4444;">
                        <i class="fas fa-sign-out-alt w-6"></i>
                        <span class="nav-label">Déconnexion</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    @if(isset($header))
                        {{ $header }}
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500">Réputation: <span class="text-indigo-600 font-bold">+0</span></p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=6366f1&color=fff" class="w-10 h-10 rounded-full border-2 border-white shadow-sm" alt="Avatar">
                </div>
            </div>

            <!-- Page Alerts -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 glass-card">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 glass-card">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>
    </body>
</html>
