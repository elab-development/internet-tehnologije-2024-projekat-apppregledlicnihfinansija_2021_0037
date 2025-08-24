<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dodaj novu transakciju
        </h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ route('transactions.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="type" class="block font-medium text-gray-700">Tip:</label>
                    <select id="type" name="type" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Izaberi tip --</option>
                        <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>Prihod</option>
                        <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Rashod</option>
                    </select>
                    @error('type')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="amount" class="block font-medium text-gray-700">Iznos (€):</label>
                    <input type="number" step="0.01" id="amount" name="amount" value="{{ old('amount') }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('amount')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="date" class="block font-medium text-gray-700">Datum:</label>
                    <input type="date" id="date" name="date" value="{{ old('date') ?? date('Y-m-d') }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category_id" class="block font-medium text-gray-700">Kategorija:</label>
                    <select id="category_id" name="category_id"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Nema kategorije --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block font-medium text-gray-700">Opis (opciono):</label>
                    <textarea id="description" name="description" rows="3"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Sačuvaj
                    </button>
                    <a href="{{ route('transactions.index') }}" class="text-gray-600 hover:text-gray-900">Nazad na transakcije</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

