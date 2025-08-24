<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dodaj novu kategoriju
        </h2>
    </x-slot>

    <div class="py-6 max-w-md mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block font-medium text-gray-700">Ime kategorije:</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('name')
                        <p class="text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Sačuvaj
                    </button>
                    <a href="{{ route('categories.index') }}" class="ml-4 text-gray-600 hover:text-gray-900">Nazad na kategorije</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

