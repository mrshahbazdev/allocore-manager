<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Allocore Manager' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; }
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #f4f4f5; color: #18181b; }
        nav { background: #18181b; color: #fff; padding: .75rem 1.5rem; display: flex; gap: 1.25rem; align-items: center; }
        nav a { color: #d4d4d8; text-decoration: none; font-size: .9rem; }
        nav a:hover { color: #fff; }
        nav .brand { font-weight: 700; color: #fff; }
        main { max-width: 72rem; margin: 1.5rem auto; padding: 0 1.5rem; }
        .card { background: #fff; border: 1px solid #e4e4e7; border-radius: .5rem; padding: 1.25rem; margin-bottom: 1.25rem; }
        h1 { font-size: 1.4rem; margin-bottom: 1rem; }
        h2 { font-size: 1.05rem; margin-bottom: .75rem; }
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        th, td { text-align: left; padding: .5rem .625rem; border-bottom: 1px solid #e4e4e7; }
        th { color: #71717a; font-weight: 600; font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; }
        .muted { color: #71717a; font-size: .85rem; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr)); gap: .75rem; margin-bottom: 1.25rem; }
        .stat { background: #fff; border: 1px solid #e4e4e7; border-radius: .5rem; padding: 1rem; }
        .stat .num { font-size: 1.6rem; font-weight: 700; }
        .stat .lbl { color: #71717a; font-size: .75rem; text-transform: uppercase; }
        .flash { background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: .6rem .9rem; border-radius: .4rem; margin-bottom: 1rem; font-size: .875rem; }
        .badge { display: inline-block; padding: .15rem .5rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }
        .b-pending { background: #fef9c3; color: #854d0e; }
        .b-accepted { background: #dbeafe; color: #1e40af; }
        .b-implemented, .b-success { background: #dcfce7; color: #166534; }
        .b-dismissed, .b-failure { background: #fee2e2; color: #991b1b; }
        .b-partial { background: #ffedd5; color: #9a3412; }
        form.inline { display: inline; }
        button, input[type=submit] { background: #18181b; color: #fff; border: 0; border-radius: .35rem; padding: .4rem .8rem; font-size: .8rem; cursor: pointer; }
        button.secondary { background: #e4e4e7; color: #18181b; }
        input, select, textarea { width: 100%; padding: .45rem .6rem; border: 1px solid #d4d4d8; border-radius: .35rem; font-size: .875rem; }
        label { display: block; font-size: .8rem; font-weight: 600; margin: .75rem 0 .25rem; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1rem; }
        .conf { font-weight: 700; color: #166534; }
        a { color: #1e40af; }
    </style>
</head>
<body>
<nav>
    <a class="brand" href="{{ route('dashboard') }}">Allocore Manager</a>
    <a href="{{ route('companies.index') }}">Companies</a>
    <a href="{{ route('clusters.index') }}">Clusters</a>
    <a href="{{ route('users.index') }}">Users</a>
    <a href="{{ route('recommendations.index') }}">Recommendations</a>
    <a href="{{ route('signals.create') }}">New Signal</a>
    <a href="{{ route('sources.index') }}">Sources</a>
    <a href="{{ route('measures.index') }}">Measures</a>
    <a href="{{ route('trends.index') }}">Trends</a>
    <a href="{{ route('challenges.index') }}">Challenges</a>
    <a href="{{ route('digest') }}">Digest</a>
    <a href="{{ route('outcomes.index') }}">Outcomes</a>
    <a href="{{ route('processes.index') }}">Automation</a>
    <a href="{{ route('intelligence.allocore') }}">Allocore</a>
    <a href="{{ route('intelligence.disavo') }}">DISAVO</a>
</nav>
<main>
    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="card" style="border-color:#fca5a5">
            @foreach ($errors->all() as $e)<div style="color:#991b1b;font-size:.85rem">{{ $e }}</div>@endforeach
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
