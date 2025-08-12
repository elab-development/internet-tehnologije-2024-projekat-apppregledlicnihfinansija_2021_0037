@extends('layouts.app')

@section('title', 'Izmeni budžet')

@section('content')
    <h1>Izmeni budžet</h1>

    @if($errors->any())
        <div style="color:red;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('budgets.update', $budget) }}" method="POST">
        @csrf
        @method('PUT')

        <label for="category_id">Kategorija:</label><br>
        <select name="category_id" required>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ $budget->category_id == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <br><br>

        <label for="amount">Iznos (€):</label><br>
        <input type="number" name="amount" step="0.01" value="{{ old('amount', $budget->amount) }}" required>
        <br><br>

        <label for="month">Mesec:</label><br>
        <input type="month" name="month" value="{{ old('month', $budget->month->format('Y-m')) }}" required>
        <br><br>

        <button type="submit">Sačuvaj izmene</button>
    </form>

    <br>
    <a href="{{ route('budgets.index') }}">Nazad na budžete</a>
@endsection
