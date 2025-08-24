<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Transakcije
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('transactions.create') }}" 
               class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Dodaj novu transakciju
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if($transactions->isEmpty())
            <p class="text-gray-600">Nema transakcija.</p>
        @else
            <div class="overflow-x-auto bg-white border border-gray-200 rounded">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <!-- Uklonjen "Naslov" -->
                            <th class="px-4 py-2 border-b">Tip</th>
                            <th class="px-4 py-2 border-b">Iznos</th>
                            <th class="px-4 py-2 border-b">Datum</th>
                            <th class="px-4 py-2 border-b">Kategorija</th>
                            <th class="px-4 py-2 border-b">Opis</th>
                            <th class="px-4 py-2 border-b">Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ ucfirst($transaction->type) }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($transaction->amount, 2) }} €
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{-- Ako nema cast na date, izbegni error --}}
                                    @php
                                        $d = $transaction->date instanceof \Illuminate\Support\Carbon
                                            ? $transaction->date
                                            : \Illuminate\Support\Carbon::parse($transaction->date);
                                    @endphp
                                    {{ $d->format('d.m.Y') }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $transaction->category->name ?? 'Nema' }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $transaction->description ?? '-' }}
                                </td>
                                <td class="px-4 py-2 border-b whitespace-nowrap">
                                    <a href="{{ route('transactions.edit', $transaction) }}" 
                                       class="text-yellow-600 hover:text-yellow-800 mr-2">Izmeni</a>
                                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Da li ste sigurni?')" 
                                                class="text-red-600 hover:text-red-800">
                                            Obriši
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Paginacija (ako koristiš ->paginate() u kontroleru) --}}
                <div class="p-4">
                    {{ $transactions->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
