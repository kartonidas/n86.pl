@extends("page")

@section("content")
    <main class="container mt-2 mb-5 regulations">
        W przygotowaniu...
        <!--
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
        -->
    </main>
@endsection