@extends('layouts.app')

@section('content')
    <h1>Ciljevi štednje</h1>

    <a href="{{ route('savings_goals.create') }}">Dodaj novi cilj</a>

    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    @if($savingsGoals->isEmpty())
        <p>Nema unetih ciljeva štednje.</p>
    @else
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Naziv</th>
                    <th>Ciljni iznos (€)</th>
                    <th>Trenutno ušteđeno (€)</th>
                    <th>Rok</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                @foreach($savingsGoals as $goal)
                    <tr>
                        <td>{{ $goal->name }}</td>
                        <td>{{ number_format($goal->target_amount, 2) }}</td>
                        <td>{{ number_format($goal->current_amount, 2) }}</td>
                        <td>{{ $goal->deadline ? $goal->deadline->format('d.m.Y') : '-' }}</td>
                        <td>
                            <a href="{{ route('savings_goals.show', $goal) }}">Prikaži</a> |
                            <a href="{{ route('savings_goals.edit', $goal) }}">Izmeni</a> |
                            <form action="{{ route('savings_goals.destroy', $goal) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Da li ste sigurni da želite da obrišete ovaj cilj?')">Obriši</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
