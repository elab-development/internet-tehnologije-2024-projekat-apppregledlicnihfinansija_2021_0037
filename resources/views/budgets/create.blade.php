@extends('layouts.app')

@section('title', 'Dodaj budžet')

@section('content')
    <h1>Dodaj novi budžet</h1>

    @if($errors->any())
        <div style="color:red;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('budgets.store') }}" method="POST">
        @csrf

        <label for="category_id">Kategorija:</label><br>
        <select name="category_id" required>
            <option value="">-- Izaberi kategoriju --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <br><br>

        <label for="amount">Iznos (€):</label><br>
        <input type="number" name="amount" step="0.01" value="{{ old('amount') }}" required>
        <br><br>

        <label for="month">Mesec:</label><br>
        <input type="month" name="month" value="{{ old('month') }}" required>
        <br><br>

        <button type="submit">Sačuvaj</button>
    </form>

    <br>
    <a href="{{ route('budgets.index') }}">Nazad na budžete</a>
@endsection
