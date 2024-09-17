<a href="{{ route('tag', $tag) }}" class="tag"
   style="background-color: {{ $tag->color }}"><i class="bi {{ $tag->icon }}"></i> {{ $tag->nameWithoutPrefix() }}</a>
