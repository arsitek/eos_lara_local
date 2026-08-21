<div class="d-flex justify-content-end mt-2">
	@foreach ($page as $index => $value)
		@if( $value != "..." )
			@foreach ($value as $item_index => $item_value)
			    <a href="{{ $item_value }}"
			       @if(request()->query('page') == $item_index)
			           onclick="event.preventDefault()"
			           class="page-link bg-blue text-dark disabled"
			       @else
			            class="page-link"
			       @endif>{{ $item_index }}</a>
			@endforeach
		@endif
	@endforeach
</div>
