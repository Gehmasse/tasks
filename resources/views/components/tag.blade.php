<a href="{{ route('tag', $tag) }}" class="tag"
   style="background-color: {{ $tag->color }}">{{ str_starts_with($tag->name, '@') ? '' : '#' }}{{ $tag->name }}</a>
