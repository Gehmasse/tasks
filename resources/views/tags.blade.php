<x-app>

    <h1>Tags</h1>

    <div class="tags">
        @foreach($tags as $tag)
            <x-tag :tag="$tag"/>
        @endforeach
    </div>

</x-app>
