<x-app>

    <h1>Filter {{ $filter->name ?: ('#' . $filter->id) }}</h1>

    <form action="{{ route('filters.update', $filter) }}" method="post">
        @csrf

        <input name="name" value="{{ $filter->name }}">
        <textarea name="filter">{!! json_encode(json_decode($filter->filter), JSON_PRETTY_PRINT) !!}</textarea>

        <input type="submit" value="Save">
    </form>

</x-app>
