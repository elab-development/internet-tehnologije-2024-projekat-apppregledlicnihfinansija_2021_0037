<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dobrodošli!
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="mb-4">Ovo je početna stranica vaše aplikacije za pregled ličnih finansija.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('budgets.index') }}" class="block bg-blue-500 text-white text-center py-3 rounded-lg hover:bg-blue-600">
                        Budžeti
                    </a>
                    <a href="{{ route('categories.index') }}" class="block bg-green-500 text-white text-center py-3 rounded-lg hover:bg-green-600">
                        Kategorije
                    </a>
                    <a href="{{ route('transactions.index') }}" class="block bg-yellow-500 text-white text-center py-3 rounded-lg hover:bg-yellow-600">
                        Transakcije
                    </a>
                    <a href="{{ route('savings_goals.index') }}" class="block bg-purple-500 text-white text-center py-3 rounded-lg hover:bg-purple-600">
                        Štedni ciljevi
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

