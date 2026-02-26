<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                @php
                    $activeColoc = auth()->user()->colocations()->wherePivotNull('left_at')->first();
                @endphp

                @if($activeColoc)
                    <h3 class="text-2xl font-bold mb-4">Bienvenue dans {{ $activeColoc->name }} !</h3>
                    <a href="{{ route('colocations.show', $activeColoc) }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg">
                        Accéder à ma Colocation
                    </a>
                @else
                    <p class="mb-4 text-gray-600">Vous n'avez pas encore de colocation active.</p>
                    <a href="{{ route('colocations.create') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg">
                        Créer une Colocation
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
