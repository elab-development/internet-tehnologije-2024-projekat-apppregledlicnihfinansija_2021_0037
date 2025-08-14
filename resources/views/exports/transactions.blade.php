<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 16px; margin: 0 0 10px; }
        .small { font-size: 11px; color: #666; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; }
        th { background: #f2f2f2; text-align: left; }
        td.num { text-align: right; }
    </style>
</head>
<body>
    <h1>Transactions report</h1>
    <div class="small">
        User: {{ $user->name ?? $user->email }} • Generated: {{ $generatedAt }}
    </div>
    @if(!empty($filters))
        <div class="small">
            Filters:
            @foreach($filters as $k => $v)
                @if(!is_null($v) && $v!=='') {{ $k }}={{ $v }}; @endif
            @endforeach
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
        @forelse($transactions as $t)
            <tr>
                <td>{{ optional($t->date)->format('Y-m-d') }}</td>
                <td>{{ ucfirst($t->type) }}</td>
                <td>{{ $t->category->name ?? '-' }}</td>
                <td class="num">{{ number_format((float)$t->amount, 2, '.', ',') }}</td>
                <td>{{ $t->description }}</td>
            </tr>
        @empty
            <tr><td colspan="5" style="text-align:center">No data</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
