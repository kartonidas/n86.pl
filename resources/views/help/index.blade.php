@extends("page")

@section("content")
    @foreach($categories as $category => $help)
        <h3>{{ $category }}</h3>
        <div>
            <ul>
                @foreach($help as $h)
                    <li><a href="{{ $h["slug"] }}">{{ $h["title"] }}</a></li>
                @endforeach
            </ul>
        </div>
    @endforeach
@endsection