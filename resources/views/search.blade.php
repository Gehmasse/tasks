<x-app>

    <h1>Search</h1>

    <form action="{{ route('tasks.search') }}" method="post">
        @csrf
        <input type="text" name="search" placeholder="Search for Title, Description and Tags">
        <input type="submit" name="Search">
    </form>

</x-app>
