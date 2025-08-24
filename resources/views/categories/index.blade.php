<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kategorije
        </h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <div class="mb-4">
                <a href="{{ route('categories.create') }}" class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Dodaj novu kategoriju
                </a>
            </div>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($categories->isEmpty())
                <p class="text-gray-600">Nema kategorija.</p>
            @else
                <ul class="list-disc list-inside space-y-2">
                    @foreach($categories as $category)
                        <li class="flex items-center justify-between">
                            <span>{{ $category->name }}</span>
                            <div class="space-x-2">
                                <a href="{{ route('categories.edit', $category) }}" class="text-blue-600 hover:underline">Izmeni</a>
                                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Da li ste sigurni?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Obriši</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-app-layout>
