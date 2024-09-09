<x-app>

    <h1>Accounts</h1>

    <form action="{{ route('remotes.store') }}" method="post">
        @csrf
        <input type="text" name="name" placeholder="Name">
        <input type="text" name="href" placeholder="URL">
        <input type="text" name="username" placeholder="User">
        <input type="password" name="password" placeholder="Password">

        <input type="submit" value="Save">
    </form>

    @forelse(App\Models\Remote::all() as $remote)
        <form action="{{ route('remotes.update', $remote) }}" method="post" id="remote-{{ $remote->id }}">
            @csrf
            <input type="text" name="name" value="{{ $remote->name }}" placeholder="Name">
            <input type="text" name="href" value="{{ $remote->href }}" placeholder="URL">
            <input type="text" name="username" value="{{ $remote->username }}" placeholder="User">
            <input type="password" name="password" value="{{ $remote->password }}" placeholder="Password">

            <div style="width: 100%; display: flex; flex-direction: row; gap: 20px">
                <input type="submit" value="Save" style="width: 50%">
                <input type="button" value="Check" style="width: 50%" class="check-btn">
            </div>

            <script>
                document.querySelector('#remote-{{ $remote->id }} .check-btn').addEventListener('click', function () {
                    checkConnectionToast(@json(route('remotes.check', $remote)))
                })
            </script>
        </form>
    @empty
        <b>No Remote Found</b>
    @endforelse

</x-app>
