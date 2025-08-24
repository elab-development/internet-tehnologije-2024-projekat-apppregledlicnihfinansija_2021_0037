<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Izmeni budžet
        </h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('budgets.update', $budget) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="category_id" class="block font-medium text-gray-700">Kategorija:</label>
                    <select name="category_id" id="category_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $budget->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="amount" class="block font-medium text-gray-700">Iznos (€):</label>
                    <input type="number" name="amount" id="amount" step="0.01" value="{{ old('amount', $budget->amount) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>

                <div>
                    <label for="month" class="block font-medium text-gray-700">Mesec:</label>
                    <input type="month" name="month" id="month" value="{{ old('month', $budget->month->format('Y-m')) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>

                <div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Sačuvaj izmene</button>
                </div>
            </form>

            <div class="mt-6">
                <a href="{{ route('budgets.index') }}" class="text-blue-600 hover:underline">Nazad na budžete</a>
            </div>
        </div>
    </div>
</x-app-layout>
