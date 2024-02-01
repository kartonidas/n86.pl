@extends("page")

@section("metatitle")
    {{ $title }}
@endsection

@section("content")
    <main class="container mt-2 mb-5 regulations">
        <h1>{!! $title !!}</h1>
        <div>
            {!! $html !!}
        </div>
    </main>
@endsection