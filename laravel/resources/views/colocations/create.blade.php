<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Créer une colocation</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="bg-red-100 text-red-800 p-4 rounded mb-4">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="bg-green-100 text-green-800 p-4 rounded mb-4">{{ session('success') }}</div>
            @endif

            <form action="{{ route('colocations.store') }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nom de la colocation</label>
                    <input type="text" name="name" id="name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Créer</button>
            </form>
        </div>
    </div>
</x-app-layout>
