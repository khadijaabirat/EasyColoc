<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $colocation->name }} ({{ ucfirst($colocation->status) }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Membres de la colocation</h3>
                <ul class="divide-y divide-gray-200">
                    @foreach($colocation->members as $member)
                        <li class="py-3 flex justify-between">
                            <span>{{ $member->name }} ({{ $member->email }})</span>
                            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">{{ $member->pivot->role }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            @php
                $isOwner = $colocation->members()->where('user_id', auth()->id())->wherePivot('role', 'owner')->exists();
            @endphp

            @if($isOwner && $colocation->status === 'active')
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border-l-4 border-green-500">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Inviter un ami</h3>
                    <form action="{{ route('invitations.store', $colocation) }}" method="POST" class="flex gap-4">
                        @csrf
                        <input type="email" name="email" placeholder="email@exemple.com" required
                               class="border-gray-300 focus:border-indigo-500 rounded-md shadow-sm flex-1">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Envoyer l'invitation
                        </button>
                    </form>
                </div>
            @endif

            <div class="flex gap-4">
                @if(!$isOwner)
                    <form action="{{ route('colocations.leave', $colocation) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded shadow" onclick="return confirm('Quitter?')">Quitter</button>
                    </form>
                @else
                    <form action="{{ route('colocations.cancel', $colocation) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded shadow" onclick="return confirm('Annuler le logement?')">Annuler la Colocation</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
