@extends('layouts.app')

@section('content')
    <h1>Dodaj novi cilj štednje</h1>

    @if($errors->any())
        <div style="color: red;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('savings_goals.store') }}" method="POST">
        @csrf

        <label for="name">Naziv cilja:</label><br>
        <input type="text" name="name" id="name" value="{{ old('name') }}" required><br><br>

        <label for="target_amount">Ciljni iznos (€):</label><br>
        <input type="number" step="0.01" name="target_amount" id="target_amount" value="{{ old('target_amount') }}" required><br><br>

        <label for="current_amount">Trenutno ušteđeno (€):</label><br>
        <input type="number" step="0.01" name="current_amount" id="current_amount" value="{{ old('current_amount', 0) }}"><br><br>

        <label for="deadline">Rok (opciono):</label><br>
        <input type="date" name="deadline" id="deadline" value="{{ old('deadline') }}"><br><br>

        <button type="submit">Sačuvaj</button>
        <a href="{{ route('savings_goals.index') }}">Otkaži</a>
    </form>
@endsection
