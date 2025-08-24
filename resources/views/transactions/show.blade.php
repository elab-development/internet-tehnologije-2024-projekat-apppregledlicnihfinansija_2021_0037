<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detalji transakcije
        </h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6 space-y-4">
            <p><strong>Tip:</strong> {{ ucfirst($transaction->type) }}</p>
            <p><strong>Iznos:</strong> {{ number_format($transaction->amount, 2) }} €</p>
            <p><strong>Datum:</strong>
                @php
                    $d = $transaction->date instanceof \Illuminate\Support\Carbon
                        ? $transaction->date
                        : \Illuminate\Support\Carbon::parse($transaction->date);
                @endphp
                {{ $d->format('d.m.Y') }}
            </p>
            <p><strong>Kategorija:</strong> {{ $transaction->category->name ?? 'Nema' }}</p>
            <p><strong>Opis:</strong> {{ $transaction->description ?? '-' }}</p>

            <div class="pt-2 flex items-center gap-4">
                <a href="{{ route('transactions.index') }}"
                   class="inline-block text-gray-700 hover:text-gray-900">Nazad na transakcije</a>

                {{-- Opcionalno: brze akcije --}}
                <a href="{{ route('transactions.edit', $transaction) }}"
                   class="inline-block text-yellow-600 hover:text-yellow-800">Izmeni</a>

                <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Da li ste sigurni?')"
                            class="text-red-600 hover:text-red-800">
                        Obriši
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
