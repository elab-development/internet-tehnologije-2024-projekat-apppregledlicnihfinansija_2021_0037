@extends('layouts.app')

@section('title', 'Kategorije')

@section('content')
    <h1>Kategorije</h1>

    <a href="{{ route('categories.create') }}">Dodaj novu kategoriju</a>

    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    @if($categories->isEmpty())
        <p>Nema kategorija.</p>
    @else
        <ul>
            @foreach($categories as $category)
                <li>
                    {{ $category->name }} 
                    <a href="{{ route('categories.edit', $category) }}">[Izmeni]</a>
                    <form action="{{ route('categories.destroy', $category) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('Da li ste sigurni?')">Obriši</button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
