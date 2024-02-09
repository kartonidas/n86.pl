@extends("page")

@section("content")
    <div class="container-fluid" id="main-page-intro">
        <main class="container pt-5">
            <h4>n86.pl</h4>
            <h1>Zarządzaj wynajmem nieruchomości</h1>
            <p class="lead">
                Zaawanoswane narzędzie do zarządzania wynajmem nieruchomości. Obsługuj wynajem nieruchomości w swoim imieniu lub w imieniu swoich klientów.
            </p>
                
            <a href="/sign-up" class="btn btn-primary round-50 mt-2 mb-2 p-3 ps-5 pe-5">Wypróbuj za darmo przez 14 dni!</a>
                
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
                @include("pages._features-box", ["current" => null])
            </div>
        </div>
            
        <div class="mt-5">
            <div class="fs-3 text-center mb-5" id="funkcje">Dostępność</div>
            <div class="row align-items-baseline justify-content-center">
                <div class="col-12 col-lg-6 round-20 text-center mb-3 mt-3 order-1 order-lg-0" style="background: #c3e6ff; max-width: 90%">
                    <img src="/images/mobile-view-mockup.png" style="max-width: 80%">
                </div>
                <div class="col-12 col-lg-6 text-center text-lg-end order-0 order-lg-1">
                    <div class="fs-4 mb-2">Urządzenia mobilne</div>
                    <div class="fs-6 text-muted">
                        Aplikacja została dostosowana do urzadzeń mobilnych.
                        Obsługa nieruchomości na telefonie lub tablecie jest równie prosta
                        co na komputerze stacjonarnym.
                    </div>
                </div>
            </div>
                
            <div class="row align-items-baseline mt-5 justify-content-center">
                <div class="col-12 col-lg-6 round-20 text-end pe-0 mt-3 order-1" style="background: #cdf1c4; max-width: 90%">
                    <img src="/images/desktop-view-mockup.png" style="max-width: 80%">
                </div>
                <div class="col-12 col-lg-6 text-center text-lg-end order-0">
                    <div class="fs-4 mb-2">Komputery osobiste</div>
                    <div class="fs-6 text-muted">
                        Program został stworzony z myślą o efektywnej pracy zarówno na komputerach stacjonarnych jaki i przenośnych.
                        Prosty interfejs zapewnia intuicyjną pracę.
                    </div>
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
                                
                            <a href="/sign-up" class="btn btn-primary round-50 mt-3 mb-5 pt-2 pb-2 ps-4 pe-4">Zarejestruj się</a>
                                
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
                                
                            <a href="/sign-up" class="btn btn-primary round-50 mt-3 mb-5 pt-2 pb-2 ps-4 pe-4">Zarejestruj się</a>
                            
                            <div class="text-muted fs-8">Podane ceny zawierają {{ config("packages.allowed.p12.vat") }}% podatku VAT.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
        
    <div class="container-fluid gray-background">
        <div class="container pt-3">
            <div class="mt-5">
                <div class="fs-3 text-center" id="kontakt">Kontakt</div>
                <div class="text-center">
                    <div class="fs-6 text-muted">{{ __("Skontaktuj się z nami poprzez poniższy formularz. Chętnie udzielimi Ci
                    pomocy.") }}</div>
                        
                    @if ($errors->any())
                        <div class="alert alert-danger text-start mt-2">
                            <ul class="list-unstyled mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                <div class="row align-items-center mt-4">
                    <div class="col-12 col-lg-6">
                        <form method="post" action="{{ route("contact") }}" class="validate d-block mt-2" onsubmit="return App.submitContactForm(this, event)">
                            <div class="row mb-3">
                                <div class="col">
                                    <label for="contactFormName" class="form-label">{{ __("Imię") }}*</label>
                                    <input name="firstname" type="text" class="form-control" id="contactFormName" data-validate="required" value="{{ $old["firstname"] ?? "" }}">
                                     <small class="input-error-info"></small>
                                </div>
                                <div class="col">
                                    <label for="contactFormLastName" class="form-label">{{ __("Nazwisko") }}*</label>
                                    <input name="lastname" type="text" class="form-control" id="contactFormLastName" data-validate="required" value="{{ $old["lastname"] ?? "" }}">
                                    <small class="input-error-info"></small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="contactFormEmail" class="form-label">{{ __("Adres e-mail") }}*</label>
                                <input name="email" type="email" class="form-control" id="contactFormEmail" data-validate="required|email" value="{{ $old["email"] ?? "" }}">
                                <small class="input-error-info"></small>
                            </div>
                            <div class="mb-3">
                                <label for="contactFormMessage" class="form-label">{{ __("Wiadomość") }}*</label>
                                <textarea name="message" class="form-control" id="contactFormMessage" rows="3" data-validate="required">{{ $old["message"] ?? "" }}</textarea>
                                <small class="input-error-info"></small>
                            </div>
                                
                            <div class="mb-3">    
                                <div class="g-recaptcha" data-callback="verifyCaptchaCallback" data-sitekey="{{ env("RECAPTCHA_KEY") }}"></div>
                                <input type="checkbox" name="captcha_response" style="display: none" value="1" data-force-validate="true" data-validate="captcha">
                                <small class="input-error-info"></small>
                            </div>
                                
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="contactFormAgree" name="contact_agree" data-validate="required">
                                    <label class="form-check-label" for="contactFormAgree">
                                        <div style="font-size:10px; line-height: 13px;">
                                            {{ __("Wyrażam zgodę na przetwarzanie moich danych osobowych przez Artur Patura – netextend.pl, w celu odpowiedzi na moje zapytanie, a także przesłania oferty handlowej (jeżeli dotyczy jej moje zapytanie). Podanie danych jest dobrowolne, ale niezbędne do udzielenia odpowiedzi oraz przygotowania oferty przez Sprzedawcę. Wycofanie zgody nie wpływa na zgodność z prawem przetwarzania, którego dokonano na podstawie zgody przed jej wycofaniem. Więcej informacji na temat przetwarzania danych znajduje się w Polityce prywatności dostępnej na stronie Serwisu internetowego.") }}
                                        </div>
                                    </label>
                                    <small class="input-error-info"></small>
                                </div>
                            </div>
                                
                            <div class="alert alert-danger d-none" role="alert" id="contact-form-errors"></div>
                                
                            <div class="mb-3 text-center">
                                <button type="submit" class="btn btn-primary">
                                    {{ __("Wyślij") }}
                                    <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span>
                                </button>
                            </div>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        </form>
                    </div>
                    <div class="col-12 col-lg-6 text-center">
                        <img src="/images/logo.svg" style="max-width: 120px;" class="mb-3">
                        <div>
                            Artur Patura - <a href="https://netextend.pl">netextend.pl</a>
                            <br/>
                            PL7712671332
                            <br/>
                            <br/>
                            <a href="mailto:biuro@n86.pl">biuro@n86.pl</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        
    <script>
        function verifyCaptchaCallback() {
            App.verifyCaptchaCallback();
        }
    </script>
@endsection