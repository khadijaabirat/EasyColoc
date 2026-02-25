<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">

        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')


                    <div class="mt-6 p-4 bg-gray-50 border rounded-lg">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Account Info</h3>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-600">Role</label>
                            <input type="text"
                                   value="{{ auth()->user()->role }}"
                                   class="w-full border rounded p-2 bg-gray-100"
                                   readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Account Status</label>
                            <input type="text"
                                   value="{{ auth()->user()->is_banned ? 'Banned' : 'Active' }}"
                                   class="w-full border rounded p-2 bg-gray-100"
                                   readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
