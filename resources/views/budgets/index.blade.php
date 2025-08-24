<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Budžeti
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <a href="{{ route('budgets.create') }}" class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Dodaj novi budžet
            </a>

            @if(session('success'))
                <div class="mb-4 text-green-600">
                    {{ session('success') }}
                </div>
            @endif

            @if($budgets->isEmpty())
                <p>Nema dodatih budžeta.</p>
            @else
                <table class="min-w-full border-collapse border border-gray-300">
                    <thead>
                        <tr>
                            <th class="border border-gray-300 px-4 py-2">Kategorija</th>
                            <th class="border border-gray-300 px-4 py-2">Iznos (€)</th>
                            <th class="border border-gray-300 px-4 py-2">Mesec</th>
                            <th class="border border-gray-300 px-4 py-2">Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($budgets as $budget)
                            <tr>
                                <td class="border border-gray-300 px-4 py-2">{{ $budget->category->name }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ number_format($budget->amount, 2) }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $budget->month->format('m.Y') }}</td>
                                <td class="border border-gray-300 px-4 py-2">
                                    <a href="{{ route('budgets.edit', $budget) }}" class="text-blue-600 hover:underline mr-2">Izmeni</a>
                                    <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Da li ste sigurni?')" class="text-red-600 hover:underline">
                                            Obriši
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-app-layout>
