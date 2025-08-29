@extends('layouts.app')

@section('title', 'Dodaj kategoriju')

@section('content')
    <h1>Dodaj novu kategoriju</h1>

    <form action="{{ route('categories.store') }}" method="POST">
        @csrf
        <label for="name">Ime kategorije:</label><br>
        <input type="text" name="name" id="name" value="{{ old('name') }}" required>
        @error('name')
            <div style="color: red;">{{ $message }}</div>
        @enderror
        <br><br>
        <button type="submit">Sačuvaj</button>
    </form>

    <br>
    <a href="{{ route('categories.index') }}">Nazad na kategorije</a>
@endsection
