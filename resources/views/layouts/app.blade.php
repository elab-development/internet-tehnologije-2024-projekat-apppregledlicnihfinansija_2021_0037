<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Lični Finansijski Pregled')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
</head>
<body>
    <nav>
        <ul>
            <li><a href="{{ route('home') }}">Početna</a></li>
            <li><a href="{{ route('categories.index') }}">Kategorije</a></li>
            <li><a href="{{ route('transactions.index') }}">Transakcije</a></li>
            <li><a href="{{ route('budgets.index') }}">Budžeti</a></li>
            <li><a href="{{ route('savings_goals.index') }}">Ciljevi štednje</a></li>
        </ul>
    </nav>

    <main>
        @if(session('success'))
            <div style="color: green;">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>

