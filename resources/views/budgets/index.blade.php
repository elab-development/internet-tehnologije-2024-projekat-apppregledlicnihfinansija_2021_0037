@extends('layouts.app')

@section('title', 'Budžeti')

@section('content')
    <h1>Moji budžeti</h1>

    <a href="{{ route('budgets.create') }}">Dodaj novi budžet</a>

    @if(session('success'))
        <div style="color:green;">{{ session('success') }}</div>
    @endif

    @if($budgets->isEmpty())
        <p>Nema dodatih budžeta.</p>
    @else
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>Kategorija</th>
                    <th>Iznos (€)</th>
                    <th>Mesec</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                @foreach($budgets as $budget)
                    <tr>
                        <td>{{ $budget->category->name }}</td>
                        <td>{{ number_format($budget->amount, 2) }}</td>
                        <td>{{ $budget->month->format('m.Y') }}</td>
                        <td>
                            <a href="{{ route('budgets.edit', $budget) }}">Izmeni</a>
                            <form action="{{ route('budgets.destroy', $budget) }}" method="POST" style="display:inline;">
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

