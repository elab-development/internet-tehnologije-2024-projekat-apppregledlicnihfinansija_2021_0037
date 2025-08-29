@extends('layouts.app')

@section('title', 'Izmeni kategoriju')

@section('content')
    <h1>Izmeni kategoriju</h1>

    <form action="{{ route('categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        <label for="name">Ime kategorije:</label><br>
        <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required>
        @error('name')
            <div style="color: red;">{{ $message }}</div>
        @enderror
        <br><br>
        <button type="submit">Sačuvaj izmene</button>
    </form>

    <br>
    <a href="{{ route('categories.index') }}">Nazad na kategorije</a>
@endsection
