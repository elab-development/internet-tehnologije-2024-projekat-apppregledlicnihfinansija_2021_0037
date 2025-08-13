<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Ciljevi štednje
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <a href="{{ route('savings_goals.create') }}" 
               class="inline-block mb-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Dodaj novi cilj
            </a>

            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($savingsGoals->isEmpty())
                <p class="text-gray-600">Nema unetih ciljeva štednje.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-4 py-2 border-b">Naziv</th>
                                <th class="text-right px-4 py-2 border-b">Ciljni iznos (€)</th>
                                <th class="text-right px-4 py-2 border-b">Trenutno ušteđeno (€)</th>
                                <th class="text-center px-4 py-2 border-b">Rok</th>
                                <th class="text-center px-4 py-2 border-b">Akcije</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($savingsGoals as $goal)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b">{{ $goal->name }}</td>
                                    <td class="px-4 py-2 border-b text-right">{{ number_format($goal->target_amount, 2) }}</td>
                                    <td class="px-4 py-2 border-b text-right">{{ number_format($goal->current_amount, 2) }}</td>
                                    <td class="px-4 py-2 border-b text-center">{{ $goal->deadline ? $goal->deadline->format('d.m.Y') : '-' }}</td>
                                    <td class="px-4 py-2 border-b text-center space-x-2">
                                        <a href="{{ route('savings_goals.show', $goal) }}" class="text-blue-600 hover:underline">Prikaži</a>
                                        <a href="{{ route('savings_goals.edit', $goal) }}" class="text-yellow-600 hover:underline">Izmeni</a>
                                        <form action="{{ route('savings_goals.destroy', $goal) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Da li ste sigurni da želite da obrišete ovaj cilj?')" class="text-red-600 hover:underline">
                                                Obriši
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
