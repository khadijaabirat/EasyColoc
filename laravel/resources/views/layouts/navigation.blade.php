<div class="flex h-screen bg-gray-50 font-sans">
    <aside class="w-64 bg-indigo-900 text-white hidden md:flex flex-col">
        <div class="p-6 text-2xl font-bold border-b border-indigo-800">🏠 EasyColoc</div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="{{ route('dashboard') }}" class="block p-3 rounded bg-indigo-800 flex items-center gap-3">
                <span>📊 Dashboard</span>
            </a>
            @if(isset($activeColoc))
            <a href="{{ route('colocations.show', $activeColoc) }}" class="block p-3 rounded hover:bg-indigo-800 flex items-center gap-3">
                <span>👥 Ma Coloc</span>
            </a>
            @endif
            <a href="#" class="block p-3 rounded hover:bg-indigo-800 flex items-center gap-3">
                <span>💸 Dépenses</span>
            </a>
            @if(auth()->user()->isGlobalAdmin())
            <a href="#" class="block p-3 rounded hover:bg-indigo-800 text-yellow-400 flex items-center gap-3">
                <span>🛡️ Admin Panel</span>
            </a>
            @endif
        </nav>
        <div class="p-4 border-t border-indigo-800 text-sm">
            Connecté en tant que: <br> <span class="font-bold">{{ auth()->user()->name }}</span>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <button class="md:hidden text-gray-600">☰</button>
            <div class="flex items-center gap-4">
                <span class="px-3 py-1 text-xs font-semibold rounded-full
                    {{ auth()->user()->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                    {{ strtoupper(auth()->user()->role) }}
                </span>
                <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center text-white font-bold">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </div>
    </main>
</div>
