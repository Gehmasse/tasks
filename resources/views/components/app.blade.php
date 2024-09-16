<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <script src="{{ asset('script.js') }}"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Tasks</title>
</head>

<body>

<nav>
    <a href="{{ route('main') }}">Main</a>
    <a href="{{ route('remotes') }}">Accounts</a>
    <a href="{{ route('calendars') }}">Calendars</a>
    <a href="#" onclick="sync()">Sync</a>
</nav>

<nav class="left">
    <a href="{{ route('tasks.all') }}">All</a>
    <a href="{{ route('tasks.today') }}">Today</a>
    <a href="{{ route('tasks.tomorrow') }}">Tomorrow</a>
    <a href="{{ route('tags') }}">Tags</a>
    <a href="{{ route('people') }}">People</a>
    <a href="{{ route('filters') }}">Filters</a>
    <a href="{{ route('search') }}">Search</a>
    <a href="{{ route('tasks.last-modified') }}">Last Modified</a>
</nav>

<main>
    {{ $slot }}

    <a href="{{ route('task.create') }}" class="new-task-button">
        <i class="bi bi-plus-lg"></i>
    </a>
</main>

<footer></footer>

<script>
    @if(session()->exists('status'))
        toast(@json(session('status')), @json(session('color', 'yellow')), actionHideAfterMs())
    @endif

    document.addEventListener('toast', e => {
        console.log(e.detail[0])
        toast(e.detail[0]?.message, e.detail[0]?.color)
    })
</script>

</body>

</html>
