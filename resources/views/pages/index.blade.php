@extends("page")

@section("content")
    <div class="container-fluid" id="main-page-intro">
        <main class="container pt-5">
            <h4>n86.pl</h4>
            <h1>Zarządzaj wynajmem nieruchomości</h1>
            <p class="lead">
                Zaawanoswane narzędzie do zarządzania wynajmem nieruchomości. Obsługuj wynajem nieruchomości w swoim imieniu lub w imieniu swoich klientów.
            </p>
                
            <a href="/sign-up" class="btn btn-primary round-50 mt-2">Wypróbuj za darmo przez pierwsze 14 dni!</a>
                
            <div class="d-flex justify-content-center justify-content-md-end">
                <img src="/images/intro-screen.png" alt="Tablica i telefon" class="w-auto" style="max-width: 100%">
            </div>
        </main>
    </div>
    
    <main class="container mt-5 mb-5">
        <div>
            <div class="fs-3 text-center" id="funkcje">Funkcjonalność</div>
                
            <p class="mt-2">
                n86.pl - to zaawansowane narzędzie ułatwiające zarządzenie wynajmem nieruchomości.
                Wynajmuj i zarządzaj obiektami w imieniu swoim lub w imieniu swoich klientów. Aplikacja umożliwia tworzenie nielimitowanej liczby umów, tworzenie szablonów dokumentów, zarządzenie rachunkami cyklicznymi (takim jak czynsz do wspólnoty mieszkaniowej/spółdzielni) oraz pozostałymi rachunkami (opłata za media, intenet).
                Wszystkie dane przechowywane są w chmurze dzięki temu dostęp aplikacji możliwy jest z każdego miejsca na ziemi zarówno na komputerze jak i na urządzeniach mobilnych.
            </p>
                
            <div class="row mt-5">
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-success disabled mb-3">
                                <i class="bi bi-building"></i>
                            </span>
                            <h5 class="card-title mb-1"><a href="{{ route("zarzadzanie_obiektami") }}" class="text-decoration-none text-muted">Zarządzanie obiektami</a></h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Zarządzenie nieruchomości zarówno w swoim imieniu jaki i w imienie swoich klientów.</div>
                                
                            <div class="text-end mt-3 card-features-readmore">
                                <a href="{{ route("zarzadzanie_obiektami") }}" class="text-decoration-none">dowiedz się więcej &raquo;</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-primary disabled mb-3">
                                <i class="bi bi-people"></i>
                            </span>
                            <h5 class="card-title mb-0"><a href="{{ route("zarzadzanie_klientami") }}" class="text-decoration-none text-muted">Zarządzanie klientami</a></h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Dodawaj klientów w imieniu których zarządzasz nieruchomościami.</div>
                            
                            <div class="text-end mt-3 card-features-readmore">
                                <a href="{{ route("zarzadzanie_klientami") }}" class="text-decoration-none">dowiedz się więcej &raquo;</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-info disabled mb-3">
                                <i class="bi bi-people"></i>
                            </span>
                            <h5 class="card-title mb-0"><a href="{{ route("zarzadzanie_najemcami") }}" class="text-decoration-none text-muted">Zarządzanie najemcami</a></h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Nieograniczona baza najemców wraz z historią przeszłych wynajmów.</div>
                                
                            <div class="text-end mt-3 card-features-readmore">
                                <a href="{{ route("zarzadzanie_najemcami") }}" class="text-decoration-none">dowiedz się więcej &raquo;</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-danger disabled mb-3">
                                <i class="bi bi-tools"></i>
                            </span>
                            <h5 class="card-title mb-0"><a href="{{ route("obsluga_usterek") }}" class="text-decoration-none text-muted">Obsługa usterek</a></h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Dodawaj zgłoszone usterki dzięki temu w prosty sposób można zlecać ich naprawę.</div>
                                
                            <div class="text-end mt-3 card-features-readmore">
                                <a href="{{ route("obsluga_usterek") }}" class="text-decoration-none">dowiedz się więcej &raquo;</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-secondary disabled mb-3">
                                <i class="bi bi-filetype-pdf"></i>
                            </span>
                            <h5 class="card-title mb-0"><a href="{{ route("generowanie_dokumentow") }}" class="text-decoration-none text-muted">Generowanie dokumentów</a></h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Generowanie dowolnych dokumentów od umów do protokołów zdawczo-odbiorczych.</div>
                                
                            <div class="text-end mt-3 card-features-readmore">
                                <a href="{{ route("generowanie_dokumentow") }}" class="text-decoration-none">dowiedz się więcej &raquo;</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-warning disabled mb-3">
                                <i class="bi bi-bell"></i>
                            </span>
                            <h5 class="card-title mb-0"><a href="{{ route("powiadomienia") }}" class="text-decoration-none text-muted">Powiadomienia</a></h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Otrzymuj powiaodomienia o przekroczonym terminie płatności.</div>
                                
                            <div class="text-end mt-3 card-features-readmore">
                                <a href="{{ route("powiadomienia") }}" class="text-decoration-none">dowiedz się więcej &raquo;</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-info disabled mb-3">
                                <i class="bi bi-bar-chart"></i>
                            </span>
                            <h5 class="card-title mb-0"><a href="{{ route("statystyki") }}" class="text-decoration-none text-muted">Statystyki</a></h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Dla każdej nieruchomści i najmu dostępne są statystyki zarobków.</div>
                            
                            <div class="text-end mt-3 card-features-readmore">
                                <a href="{{ route("statystyki") }}" class="text-decoration-none">dowiedz się więcej &raquo;</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-dark disabled mb-3">
                                <i class="bi bi-currency-dollar"></i>
                            </span>
                            <h5 class="card-title mb-0"><a href="{{ route("kontrola_rachunkow") }}" class="text-decoration-none text-muted">Kontrola rachunków</a></h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Dodawaj rachunki dzięki temu nie przeoczysz żadnej płatności.</div>
                                
                            <div class="text-end mt-3 card-features-readmore">
                                <a href="{{ route("kontrola_rachunkow") }}" class="text-decoration-none">dowiedz się więcej &raquo;</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-success disabled mb-3">
                                <i class="bi bi-globe-americas"></i>
                            </span>
                            <h5 class="card-title mb-0"><a href="{{ route("rozwiazania_chmurowe") }}" class="text-decoration-none text-muted">Rozwiązanie chmurowe</a></h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Dzięki przechowywaniu danych w chmurze, dane dostępne są z każdego miejsca na ziemi.</div>
                                
                            <div class="text-end mt-3 card-features-readmore">
                                <a href="{{ route("rozwiazania_chmurowe") }}" class="text-decoration-none">dowiedz się więcej &raquo;</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            
        <div class="mt-5">
            <div class="fs-3 text-center mb-5" id="funkcje">Dostępność</div>
            <div class="row align-items-baseline">
                <div class="col-12 col-lg-6 round-20 text-center mb-3" style="background: #c3e6ff">
                    <img src="/images/mobile-view-mockup.png" style="max-width: 80%">
                </div>
                <div class="col-12 col-lg-6 text-center text-lg-end">
                    <div class="fs-4 mb-2">Urządzenia mobilne</div>
                    <div class="fs-7 text-muted">
                        Aplikacja została dostosowana do urzadzeń mobilnych.
                        <br/>
                        Obsługa nieruchomości na telefonie lub tablecie jest równie prosta
                        <br/>
                        co na komputerze stacjonarnym.
                    </div>
                </div>
            </div>
                
            <div class="row align-items-baseline mt-5">
                <div class="col-12 col-lg-6 text-center text-lg-end">
                    <div class="fs-4 mb-2">Komputery osobiste</div>
                    <div class="fs-7 text-muted">
                        Aplikacja została dostosowana do urzadzeń mobilnych.
                        <br/>
                        Obsługa nieruchomości na telefonie lub tablecie jest równie prosta
                        <br/>
                        co na komputerze stacjonarnym.
                    </div>
                </div>
                <div class="col-12 col-lg-6 round-20 text-center mt-3" style="background: #cdf1c4">
                    <img src="/images/desktop-view-mockup.png" style="max-width: 80%">
                </div>
            </div>
        </div>
        
        <div class="mt-5">
            <div class="fs-3 text-center" id="cennik">Cennik</div>
            
            <div class="text-muted text-center">
                Podane ceny pakietu dotyczą zarządzania jedną nieruchomością.
            </div>
            
            <div class="row mt-4">
                <div class="col-12 col-lg-6 text-center">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <div class="h4">Płatność miesięczna</div>
                                
                            <div class="lh-1 mt-3 mb-3">
                                <span class="text-primary fs-1 fw-bold">{{ config("packages.allowed.p1.price_vat") }} zł</span>
                                <div class="text-muted">brutto / miesięcznie</div>
                            </div>
                                
                            <ul class="list-unstyled mt-4 fs-7 text-start lh-lg">
                                <li><i class="bi bi-check-lg text-success"></i> Nielimitowana ilość klientów i najemców,</li>
                                <li><i class="bi bi-check-lg text-success"></i> Nielimitowana ilość umów i dokumentów,</li>
                                <li><i class="bi bi-check-lg text-success"></i> Szablony dokumentów,</li>
                                <li><i class="bi bi-check-lg text-success"></i> Powiadomienia,</li>
                                <li><i class="bi bi-check-lg text-success"></i> Zarządzanie nieruchomością (rachunki, wynajmowanie),</li>
                                <li><i class="bi bi-check-lg text-success"></i> Nielimitowana ilość użytkowników,</li>
                            </ul>
                                
                            <a href="/sign-up" class="btn btn-primary round-50 mt-3 mb-5">Zarejestruj się</a>
                                
                            <div class="text-muted fs-8">Podane ceny zawierają {{ config("packages.allowed.p12.vat") }}% podatku VAT.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6 text-center">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <div class="h4">Płatność roczna</div>
                                
                            <div class="lh-1 mt-3 mb-3">
                                <span class="text-primary fs-1 fw-bold">{{ config("packages.allowed.p12.price_vat") }} zł</span>
                                <div class="text-muted">brutto / rocznie</div>
                            </div>
                            
                            <ul class="list-unstyled mt-4 fs-7 text-start lh-lg">
                                <li><i class="bi bi-check-lg text-success"></i> Nielimitowana ilość klientów i najemców,</li>
                                <li><i class="bi bi-check-lg text-success"></i> Nielimitowana ilość umów i dokumentów,</li>
                                <li><i class="bi bi-check-lg text-success"></i> Szablony dokumentów,</li>
                                <li><i class="bi bi-check-lg text-success"></i> Powiadomienia,</li>
                                <li><i class="bi bi-check-lg text-success"></i> Zarządzanie nieruchomością (rachunki, wynajmowanie),</li>
                                <li><i class="bi bi-check-lg text-success"></i> Nielimitowana ilość użytkowników,</li>
                            </ul>
                                
                            <a href="/sign-up" class="btn btn-primary round-50 mt-3 mb-5">Zarejestruj się</a>
                            
                            
                            <div class="text-muted fs-8">Podane ceny zawierają {{ config("packages.allowed.p12.vat") }}% podatku VAT.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection