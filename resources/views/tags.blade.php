<x-app>

    <h1>Tags</h1>

    <div class="tag-list">
        @foreach($tags as $tag)
            <x-tag :tag="$tag"/>
        @endforeach
    </div>

</x-app>
