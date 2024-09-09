<x-app>
    @foreach($filters as $route => $title)
        <div><a href="{{ route($route) }}">{{ $title }}</a></div>
    @endforeach
</x-app>
