@extends('layouts.app')

@section('title', 'Transakcije')

@section('content')
    <h1>Transakcije</h1>

    <a href="{{ route('transactions.create') }}">Dodaj novu transakciju</a>

    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    @if($transactions->isEmpty())
        <p>Nema transakcija.</p>
    @else
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>Naslov</th>
                    <th>Tip</th>
                    <th>Iznos</th>
                    <th>Datum</th>
                    <th>Kategorija</th>
                    <th>Opis</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->title }}</td>
                        <td>{{ ucfirst($transaction->type) }}</td>
                        <td>{{ number_format($transaction->amount, 2) }} €</td>
                        <td>{{ $transaction->date->format('d.m.Y') }}</td>
                        <td>{{ $transaction->category->name ?? 'Nema' }}</td>
                        <td>{{ $transaction->description ?? '-' }}</td>
                        <td>
                            <a href="{{ route('transactions.edit', $transaction) }}">Izmeni</a>
                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Da li ste sigurni?')">Obriši</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
