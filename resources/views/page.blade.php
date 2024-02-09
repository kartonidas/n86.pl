<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <link rel="icon" href="/favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield("metatitle", "Aplikacja do zarządzania wynajmem")</title>
        <meta name="description" content="@yield("metadesc", "Aplikacja do zarządzania wynajmem: mieszkań, domów, lokali usługowych, komercyjnych. Generowanie umów, umów okazjonalnych, zarządzanie najemcami, analiza zysków i kosztów generowanych przez nieruchomość. Obsługa niercuhomośći w imieniu klientów. Zarządzanie kosztami generowanymi przez obiekt, przypomnienia o płatnościach.")">
            
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        
        <link rel="stylesheet" href="/css/page.css">
        <link rel="stylesheet" href="/js/magnific-popup/dist/magnific-popup.css">
        <script src='//www.google.com/recaptcha/api.js'></script>
        
        <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
        <link rel="manifest" href="/favicon/site.webmanifest">
        <link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#da532c">
        <meta name="theme-color" content="#ffffff">
    </head>
    <body>
        <nav class="navbar navbar-expand-md ">
            <div class="container pt-3 pb-3 mb-3">
                <a class="navbar-brand" href="/">
                    <img src="/images/logo.svg" style="max-width: 50px;" class="me-3">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav me-auto mb-2 mb-md-0">
                        <li class="nav-item">
                            <a class="nav-link text-body fw-500" href="/">Start</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-body fw-500" href="/#funkcje">Funkcje</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-body fw-500" href="/#cennik">Cennik</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-body fw-500" href="/#kontakt">Kontakt</a>
                        </li>
                    </ul>
                        
                    <div class="d-block d-lg-none navbar-divider mb-3"></div>
                    <div class="text-center text-lg-start">
                        <a href="/sign-in" class="btn round-50 fw-500 text-primary me-4">Zaloguj się</a>
                        <a href="/sign-up" class="btn btn-primary fw-500 round-50">Zarejestruj się</a>
                    </div>
                </div>
            </div>
        </nav>
        @yield("content")
        
        <footer>
            <div class="container-fluid gray-background">
                <div class="container pt-5 pb-5">
                    <div class="row text-center text-sm-start">
                        <div class="col-12 col-sm-6 col-lg-8">
                            <img src="/images/logo.svg" style="max-width: 50px;" class="mb-3">
                        </div>
                        <div class="col-12 col-sm-3 col-lg-2">
                            <div class="fw-bold h5">O nas</div>
                            <ul class="list-unstyled">
                                <li class="mt-1 mb-1">
                                    <a href="" class="text-body text-decoration-none">O nas</a>
                                </li>
                                <li class="mt-1 mb-1">
                                    <a href="/#kontakt" class="text-body text-decoration-none">Kontakt</a>
                                </li>
                                <li class="mt-1 mb-1">
                                    <a href="{{ route("help") }}" class="text-body text-decoration-none">Pomoc</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-12 col-sm-3 col-lg-2">
                            <div class="fw-bold h5">Dokumenty</div>
                            <ul class="list-unstyled">
                                <li class="mt-1 mb-1">
                                    <a href="{{ route("regulations") }}" class="text-body text-decoration-none">Regulamin</a>
                                </li>
                                <li class="mt-1 mb-1">
                                    <a href="{{ route("privacy_policy") }}" class="text-body text-decoration-none">Polityka prywatności</a>
                                </li>
                                <li class="mt-1 mb-1">
                                    <a href="{{ route("cookies") }}" class="text-body text-decoration-none">Ciasteczka</a>
                                </li>
                            </ul>
                        </div>        
                    </div>
                </div>
            </div>
        </footer>
    </body>
    
    <script>
        LOCALE = "pl";
    </script>
    <script src="/js/jquery/jquery.min.js"></script>
    <script src="/js/magnific-popup/dist/jquery.magnific-popup.min.js"></script>
    <script src="/js/functions.js"></script>
    <script src="/js/app.js"></script>
    <script src="/js/validator.js"></script>
</html>
