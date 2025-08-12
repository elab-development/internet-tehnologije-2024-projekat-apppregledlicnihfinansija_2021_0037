@extends('layouts.app')

@section('content')
    <h1>Detalji cilja štednje</h1>

    <p><strong>Naziv:</strong> {{ $savingsGoal->name }}</p>
    <p><strong>Ciljni iznos:</strong> {{ number_format($savingsGoal->target_amount, 2) }} €</p>
    <p><strong>Trenutno ušteđeno:</strong> {{ number_format($savingsGoal->current_amount, 2) }} €</p>
    <p><strong>Rok:</strong> {{ $savingsGoal->deadline ? $savingsGoal->deadline->format('d.m.Y') : '-' }}</p>

    <a href="{{ route('savings_goals.edit', $savingsGoal) }}">Izmeni</a> |
    <a href="{{ route('savings_goals.index') }}">Nazad na listu</a>
@endsection
