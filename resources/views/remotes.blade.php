<x-app>

    <h1>Accounts</h1>

    <form action="{{ route('remotes.store') }}" method="post" class="large">

        @csrf

        <input type="text" name="name" placeholder="Account name, e. g. 'Nextcloud'">
        <input type="text" name="href" placeholder="URL, e. g. 'https://next.cloud/remote.php/dav/calendars/Username'">
        <input type="text" name="username" placeholder="Username">
        <input type="password" name="password" placeholder="Password">

        <input type="submit" value="Save">

    </form>

    @forelse($remotes as $remote)
        <form action="{{ route('remotes.update', $remote) }}" method="post" id="remote-{{ $remote->id }}" class="large">

            @csrf

            <input type="text" name="name" value="{{ $remote->name }}" placeholder="Name">
            <input type="text" name="href" value="{{ $remote->href }}" placeholder="URL">
            <input type="text" name="username" value="{{ $remote->username }}" placeholder="User">
            <input type="password" name="password" value="{{ $remote->password }}" placeholder="Password">

            <div class="btn-line">
                <input type="submit" value="Save">
                <input type="button" value="Check" class="check-btn">
                <a class="btn secondary" href="{{ route('calendars', $remote) }}">Calendars</a>
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
