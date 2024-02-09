@extends("page")

@section("metatitle"){{ $meta["title"] }}@endsection
@section("metadesc"){{ $meta["description"] }}@endsection

@section("content")
    <main class="container mt-2 mb-5 regulations">
        <h2>{!! $title !!}</h2>
        @if($subtitle)
            <h5 class="fw-normal text-muted fs-6">{!! $subtitle !!}</h5>
        @endif
        <div class="mt-5 mb-5">
            {!! $html !!}
        </div>
        
        <div class="mt-5">
            <div class="fs-4 mb-3 text-center">Dowiedz się więcej:</div>
            <div class="row">
                @include("pages._features-box", ["current" => $current])
            </div>
        </div>    
    </main>
@endsection