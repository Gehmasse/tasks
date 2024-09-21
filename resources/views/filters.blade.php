<x-app>

    <form action="{{ route('filters.store') }}" method="post">
        @csrf
        <input type="submit" value="Create New Filter">
    </form>

    @foreach($filters as $filter)
        <div>
            <b>{{ $filter->name }}</b>
            <a href="{{ route('filters.show', $filter) }}">Edit</a>
            <a href="{{ route('tasks.filter', $filter) }}">Show</a>
        </div>
    @endforeach
</x-app>
