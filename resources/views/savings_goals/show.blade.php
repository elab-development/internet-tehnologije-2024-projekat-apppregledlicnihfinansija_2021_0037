<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detalji cilja štednje
        </h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
            <p><strong>Naziv:</strong> {{ $savingsGoal->name }}</p>
            <p><strong>Ciljni iznos:</strong> {{ number_format($savingsGoal->target_amount, 2) }} €</p>
            <p><strong>Trenutno ušteđeno:</strong> {{ number_format($savingsGoal->current_amount, 2) }} €</p>
            <p><strong>Rok:</strong> {{ $savingsGoal->deadline ? $savingsGoal->deadline->format('d.m.Y') : '-' }}</p>

            <div class="flex space-x-4 pt-4">
                <a href="{{ route('savings_goals.edit', $savingsGoal) }}"
                   class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    Izmeni
                </a>
                <a href="{{ route('savings_goals.index') }}"
                   class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                    Nazad na listu
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
