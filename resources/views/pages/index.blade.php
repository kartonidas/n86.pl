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
            <div class="fs-3 text-center" id="app-features">Funkcjonalność</div>
                
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
                            <h5 class="card-title mb-1">Zarządzanie obiektami</h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Zarządzenie nieruchomości zarówno w swoim imieniu jaki i w imienie swoich klientów.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-primary disabled mb-3">
                                <i class="bi bi-people"></i>
                            </span>
                            <h5 class="card-title mb-0">Zarządzanie klientami</h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Dodawaj klientów w imieniu których zarządzasz nieruchomościami.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-info disabled mb-3">
                                <i class="bi bi-people"></i>
                            </span>
                            <h5 class="card-title mb-0">Zarządzanie najemcami</h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Nieograniczona baza najemców wraz z historią przeszłych wynajmów.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-danger disabled mb-3">
                                <i class="bi bi-tools"></i>
                            </span>
                            <h5 class="card-title mb-0">Obsługa usterek</h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Dodawaj zgłoszone usterki dzięki temu w prosty sposób można zlecać ich naprawę.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-secondary disabled mb-3">
                                <i class="bi bi-filetype-pdf"></i>
                            </span>
                            <h5 class="card-title mb-0">Generowanie dokumentów</h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Generowanie dowolnych dokumentów od umów do protokołów zdawczo-odbiorczych.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-warning disabled mb-3">
                                <i class="bi bi-bell"></i>
                            </span>
                            <h5 class="card-title mb-0">Powiadomienia</h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Otrzymuj powiaodomienia o przekroczonym terminie płatności.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-info disabled mb-3">
                                <i class="bi bi-bar-chart"></i>
                            </span>
                            <h5 class="card-title mb-0">Statystyki</h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Dla każdej nieruchomści i najmu dostępne są statystyki zarobków.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-dark disabled mb-3">
                                <i class="bi bi-currency-dollar"></i>
                            </span>
                            <h5 class="card-title mb-0">Kontrola rachunków</h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Dodawaj rachunki dzięki temu nie przeoczysz żadnej płatności.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shadow p-3 mb-3 bg-white rounded">
                        <div class="card-body">
                            <span class="btn btn-success disabled mb-3">
                                <i class="bi bi-globe-americas"></i>
                            </span>
                            <h5 class="card-title mb-0">Rozwiązanie chmurowe</h5>
                            <div class="card-text card-features-desc text-muted lh-sm">Dzięki przechowywaniu danych w chmurze, dane dostępne są z każdego miejsca na ziemi.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-5">
            <div class="fs-3 text-center" id="app-prices">Cennik</div>
        </div>
    </main>
@endsection