<x-app>

    <h1>{{ $title }}</h1>

    <div class="tag-list">
        @foreach($tags as $tag)
            <x-tag :tag="$tag"/>
        @endforeach
    </div>

</x-app>
