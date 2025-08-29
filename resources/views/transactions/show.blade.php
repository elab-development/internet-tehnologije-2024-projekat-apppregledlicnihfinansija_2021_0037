@extends('layouts.app')

@section('title', 'Detalji transakcije')

@section('content')
    <h1>Detalji transakcije</h1>

    <p><strong>Naslov:</strong> {{ $transaction->title }}</p>
    <p><strong>Tip:</strong> {{ ucfirst($transaction->type) }}</p>
    <p><strong>Iznos:</strong> {{ number_format($transaction->amount, 2) }} €</p>
    <p><strong>Datum:</strong> {{ $transaction->date->format('d.m.Y') }}</p>
    <p><strong>Kategorija:</strong> {{ $transaction->category->name ?? 'Nema' }}</p>
    <p><strong>Opis:</strong> {{ $transaction->description ?? '-' }}</p>

    <a href="{{ route('transactions.index') }}">Nazad na transakcije</a>
@endsection
