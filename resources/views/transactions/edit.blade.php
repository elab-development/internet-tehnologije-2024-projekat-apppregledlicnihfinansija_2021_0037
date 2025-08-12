@extends('layouts.app')

@section('title', 'Izmeni transakciju')

@section('content')
    <h1>Izmeni transakciju</h1>

    <form action="{{ route('transactions.update', $transaction) }}" method="POST">
        @csrf
        @method('PUT')

        <label for="title">Naslov:</label><br>
        <input type="text" id="title" name="title" value="{{ old('title', $transaction->title) }}" required>
        @error('title')<div style="color:red;">{{ $message }}</div>@enderror
        <br><br>

        <label>Tip:</label><br>
        <select name="type" required>
            <option value="income" {{ old('type', $transaction->type) == 'income' ? 'selected' : '' }}>Prihod</option>
            <option value="expense" {{ old('type', $transaction->type) == 'expense' ? 'selected' : '' }}>Rashod</option>
        </select>
        @error('type')<div style="color:red;">{{ $message }}</div>@enderror
        <br><br>

        <label for="amount">Iznos (€):</label><br>
        <input type="number" step="0.01" id="amount" name="amount" value="{{ old('amount', $transaction->amount) }}" required>
        @error('amount')<div style="color:red;">{{ $message }}</div>@enderror
        <br><br>

        <label for="date">Datum:</label><br>
        <input type="date" id="date" name="date" value="{{ old('date', $transaction->date->format('Y-m-d')) }}" required>
        @error('date')<div style="color:red;">{{ $message }}</div>@enderror
        <br><br>

        <label for="category_id">Kategorija:</label><br>
        <select name="category_id">
            <option value="">-- Nema kategorije --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')<div style="color:red;">{{ $message }}</div>@enderror
        <br><br>

        <label for="description">Opis (opciono):</label><br>
        <textarea id="description" name="description">{{ old('description', $transaction->description) }}</textarea>
        @error('description')<div style="color:red;">{{ $message }}</div>@enderror
        <br><br>

        <button type="submit">Sačuvaj izmene</button>
    </form>

    <br>
    <a href="{{ route('transactions.index') }}">Nazad na transakcije</a>
@endsection
