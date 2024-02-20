@extends("page")

@section("metatitle")
    Pomoc: {{ $category }} - {{ $title }}
@endsection

@section("content")
    <main class="container mt-2 mb-5 regulations">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item" aria-current="page"><a href="/">Strona główna</a></li>
                <li class="breadcrumb-item" aria-current="page"><a href="/pomoc">Pomoc</a></li>
                <li class="breadcrumb-item" aria-current="page">{{ $category }}</li>
                <li class="breadcrumb-item active" aria-current="page">{!! $title !!}</li>
            </ol>
        </nav>
    
        <h1 class="fs-3 mb-4">{!! $title !!}</h1>
        <div class="fs-6">
            {!! $help !!}
        </div>
    </main>
@endsection