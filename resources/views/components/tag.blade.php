<a href="{{ route('tag', $tag) }}" class="tag" style="background-color: {{ App\Tags::color($tag) }}">{{ str_starts_with($tag, '@') ? '' : '#' }}{{ $tag }}</a>
