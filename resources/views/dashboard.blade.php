<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Statistika --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ukupni prihodi</p>
                    <p class="text-2xl font-bold text-green-600">
                        €{{ number_format($totalIncome, 2) }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ukupni rashodi</p>
                    <p class="text-2xl font-bold text-red-600">
                        €{{ number_format($totalExpense, 2) }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Trenutno stanje</p>
                    <p class="text-2xl font-bold text-blue-600">
                        €{{ number_format($totalIncome - $totalExpense, 2) }}
                    </p>
                </div>
            </div>

            {{-- Poslednje transakcije --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Poslednje transakcije</h3>
                @if($latestTransactions->isEmpty())
                    <p class="text-gray-500">Nema transakcija.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Naslov</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tip</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Iznos</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Datum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($latestTransactions as $transaction)
                                <tr>
                                    <td class="px-4 py-2">{{ $transaction->title }}</td>
                                    <td class="px-4 py-2">
                                        @if($transaction->type === 'income')
                                            <span class="text-green-600">Prihod</span>
                                        @else
                                            <span class="text-red-600">Rashod</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">€{{ number_format($transaction->amount, 2) }}</td>
                                    <td class="px-4 py-2">{{ $transaction->date->format('d.m.Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- Ciljevi štednje --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Ciljevi štednje</h3>
                @if($savingsGoals->isEmpty())
                    <p class="text-gray-500">Nema ciljeva štednje.</p>
                @else
                    <div class="space-y-4">
                        @foreach($savingsGoals as $goal)
                            <div>
                                <p class="font-medium">{{ $goal->name }}</p>
                                <div class="w-full bg-gray-200 rounded-full h-4 mt-1">
                                    @php
                                        $progress = min(100, ($goal->current_amount / $goal->target_amount) * 100);
                                    @endphp
                                    <div class="bg-green-500 h-4 rounded-full" style="width: {{ $progress }}%"></div>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    €{{ number_format($goal->current_amount, 2) }} / €{{ number_format($goal->target_amount, 2) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

