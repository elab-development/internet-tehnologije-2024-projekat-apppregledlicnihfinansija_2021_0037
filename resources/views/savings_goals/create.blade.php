<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dodaj novi cilj štednje
        </h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('savings_goals.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block font-medium text-gray-700">Naziv cilja:</label>
                    <input type="text" name="name" id="name" required
                           value="{{ old('name') }}"
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="target_amount" class="block font-medium text-gray-700">Ciljni iznos (€):</label>
                    <input type="number" step="0.01" name="target_amount" id="target_amount" required
                           value="{{ old('target_amount') }}"
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="current_amount" class="block font-medium text-gray-700">Trenutno ušteđeno (€):</label>
                    <input type="number" step="0.01" name="current_amount" id="current_amount"
                           value="{{ old('current_amount', 0) }}"
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="deadline" class="block font-medium text-gray-700">Rok (opciono):</label>
                    <input type="date" name="deadline" id="deadline"
                           value="{{ old('deadline') }}"
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="flex items-center space-x-4">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Sačuvaj
                    </button>
                    <a href="{{ route('savings_goals.index') }}" class="text-gray-600 hover:underline">
                        Otkaži
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
