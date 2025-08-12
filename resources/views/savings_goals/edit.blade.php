@extends('layouts.app')

@section('content')
    <h1>Izmeni cilj štednje</h1>

    @if($errors->any())
        <div style="color: red;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('savings_goals.update', $savingsGoal) }}" method="POST">
        @csrf
        @method('PUT')

        <label for="name">Naziv cilja:</label><br>
        <input type="text" name="name" id="name" value="{{ old('name', $savingsGoal->name) }}" required><br><br>

        <label for="target_amount">Ciljni iznos (€):</label><br>
        <input type="number" step="0.01" name="target_amount" id="target_amount" value="{{ old('target_amount', $savingsGoal->target_amount) }}" required><br><br>

        <label for="current_amount">Trenutno ušteđeno (€):</label><br>
        <input type="number" step="0.01" name="current_amount" id="current_amount" value="{{ old('current_amount', $savingsGoal->current_amount) }}"><br><br>

        <label for="deadline">Rok (opciono):</label><br>
        <input type="date" name="deadline" id="deadline" value="{{ old('deadline', $savingsGoal->deadline ? $savingsGoal->deadline->format('Y-m-d') : '') }}"><br><br>

        <button type="submit">Sačuvaj izmene</button>
        <a href="{{ route('savings_goals.index') }}">Otkaži</a>
    </form>
@endsection
