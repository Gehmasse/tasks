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

<main>
    {{ $slot }}

    <div class="quick-menu">
        <a href="{{ route('task.create') }}">
            <i class="bi bi-plus-circle-fill"></i>
        </a>

        <a href="{{ route('tasks.today') }}">
            <i class="bi bi-list-check"></i>
        </a>

        <a href="{{ route('filters') }}">
            <i class="bi bi-funnel-fill"></i>
        </a>

        <a href="{{ route('tags') }}">
            <i class="bi bi-tags-fill"></i>
        </a>

        <a href="{{ route('people') }}">
            <i class="bi bi-people-fill"></i>
        </a>

        <a href="{{ route('search') }}">
            <i class="bi bi-search"></i>
        </a>

        <a href="#" onclick="sync()">
            <i class="bi bi-arrow-repeat"></i>
        </a>

        <a href="{{ route('settings') }}">
            <i class="bi bi-gear-fill"></i>
        </a>
    </div>

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
