@extends("page")

@section("metatitle")
    Pomoc
@endsection

@section("content")
    <main class="container mt-2 mb-5 regulations">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item" aria-current="page"><a href="/">Strona główna</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pomoc</li>
            </ol>
        </nav>
        
        <h1 class="fs-3 mb-4">Pomoc</h1>
        
        <p class="fs-6">
            Poniżej znajdziesz kilka artykułów, które pomogą w rozpoczęciu pracy z aplikacją.
            Jeśli nie znalazłeś odpowiedzi na nurtujące Cię pytania, napisz do nas, korzystając z formularza
            kontaktowego lub skorzystaj z dostępnego na stronie czatu.
        </p>
        
        <div class="row mt-4">
            @foreach($categories as $category => $help)
                <div class="col-12 col-sm-4 mb-4">
                    <div class="shadow h-100 p-4">
                        <h3 class="fs-5">{{ $category }}</h3>
                        <div>
                            <ul class="list-unstyled">
                                @foreach($help as $h)
                                    @foreach($h as $h2)
                                    <li>- <a href="{{ $h2["slug"] }}">{{ $h2["title"] }}</a></li>
                                    @endforeach
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </main>
@endsection