<div class="col-12 col-sm-6 col-lg-4 mb-3 align-items-stretch @if($current == "zarzadzanie-obiektami"){{ "d-none" }}@else{{ "d-block d-sm-flex" }}@endif">
    <a href="{{ route("zarzadzanie_obiektami") }}" class="text-decoration-none">
        <div class="card shadow p-3 bg-white rounded h-100 w-100">
            <div class="card-body">
                <span class="btn btn-success disabled mb-3">
                    <i class="bi bi-building"></i>
                </span>
                <h5 class="card-title mb-1 text-muted">Zarządzanie obiektami</h5>
                <div class="card-text card-features-desc text-muted lh-sm">Zarządzenie nieruchomości zarówno w swoim imieniu jaki i w imienie swoich klientów.</div>
                    
                <div class="text-end mt-3 card-features-readmore">
                    <span class="more-info">dowiedz się więcej &raquo;</span>
                </div>
            </div>
        </div>
    </a>
</div>
<div class="col-12 col-sm-6 col-lg-4 mb-3 align-items-stretch @if($current == "zarzadzanie-klientami"){{ "d-none" }}@else{{ "d-block d-sm-flex" }}@endif">
    <a href="{{ route("zarzadzanie_klientami") }}" class="text-decoration-none">
        <div class="card shadow p-3 bg-white rounded h-100 w-100">
            <div class="card-body">
                <span class="btn btn-primary disabled mb-3">
                    <i class="bi bi-people"></i>
                </span>
                <h5 class="card-title mb-0 text-muted">Zarządzanie klientami</h5>
                <div class="card-text card-features-desc text-muted lh-sm">Dodawaj klientów w imieniu których zarządzasz nieruchomościami.</div>
                
                <div class="text-end mt-3 card-features-readmore">
                    <span class="more-info">dowiedz się więcej &raquo;</span>
                </div>
            </div>
        </div>
    </a>
</div>
<div class="col-12 col-sm-6 col-lg-4 mb-3 align-items-stretch @if($current == "zarzadzanie-najemcami"){{ "d-none" }}@else{{ "d-block d-sm-flex" }}@endif">
    <a href="{{ route("zarzadzanie_najemcami") }}" class="text-decoration-none">
        <div class="card shadow p-3 bg-white rounded h-100 w-100">
            <div class="card-body">
                <span class="btn btn-info disabled mb-3">
                    <i class="bi bi-people"></i>
                </span>
                <h5 class="card-title mb-0 text-muted">Zarządzanie najemcami</h5>
                <div class="card-text card-features-desc text-muted lh-sm">Nieograniczona baza najemców wraz z historią przeszłych wynajmów.</div>
                    
                <div class="text-end mt-3 card-features-readmore">
                    <span class="more-info">dowiedz się więcej &raquo;</span>
                </div>
            </div>
        </div>
    </a>
</div>
<div class="col-12 col-sm-6 col-lg-4 mb-3 align-items-stretch @if($current == "obsluga-usterek"){{ "d-none" }}@else{{ "d-block d-sm-flex" }}@endif">
    <a href="{{ route("obsluga_usterek") }}" class="text-decoration-none">
        <div class="card shadow p-3 bg-white rounded h-100 w-100">
            <div class="card-body">
                <span class="btn btn-danger disabled mb-3">
                    <i class="bi bi-tools"></i>
                </span>
                <h5 class="card-title mb-0 text-muted">Obsługa usterek</h5>
                <div class="card-text card-features-desc text-muted lh-sm">Dodawaj zgłoszone usterki dzięki temu w prosty sposób można zlecać ich naprawę.</div>
                    
                <div class="text-end mt-3 card-features-readmore">
                    <span class="more-info">dowiedz się więcej &raquo;</span>
                </div>
            </div>
        </div>
    </a>
</div>
<div class="col-12 col-sm-6 col-lg-4 mb-3 align-items-stretch @if($current == "generowanie-dokumentow"){{ "d-none" }}@else{{ "d-block d-sm-flex" }}@endif">
    <a href="{{ route("generowanie_dokumentow") }}" class="text-decoration-none">
        <div class="card shadow p-3 bg-white rounded h-100 w-100">
            <div class="card-body">
                <span class="btn btn-secondary disabled mb-3">
                    <i class="bi bi-filetype-pdf"></i>
                </span>
                <h5 class="card-title mb-0 text-muted">Generowanie dokumentów</h5>
                <div class="card-text card-features-desc text-muted lh-sm">Generowanie dowolnych dokumentów od umów do protokołów zdawczo-odbiorczych.</div>
                    
                <div class="text-end mt-3 card-features-readmore">
                    <span class="more-info">dowiedz się więcej &raquo;</span>
                </div>
            </div>
        </div>
    </a>
</div>
<div class="col-12 col-sm-6 col-lg-4 mb-3 align-items-stretch @if($current == "powiadomienia"){{ "d-none" }}@else{{ "d-block d-sm-flex" }}@endif">
    <a href="{{ route("powiadomienia") }}" class="text-decoration-none">
        <div class="card shadow p-3 bg-white rounded h-100 w-100">
            <div class="card-body">
                <span class="btn btn-warning disabled mb-3">
                    <i class="bi bi-bell"></i>
                </span>
                <h5 class="card-title mb-0 text-muted">Powiadomienia</h5>
                <div class="card-text card-features-desc text-muted lh-sm">Otrzymuj powiaodomienia o przekroczonym terminie płatności.</div>
                    
                <div class="text-end mt-3 card-features-readmore">
                    <span class="more-info">dowiedz się więcej &raquo;</span>
                </div>
            </div>
        </div>
    </a>
</div>
<div class="col-12 col-sm-6 col-lg-4 mb-3 align-items-stretch @if($current == "statystyki"){{ "d-none" }}@else{{ "d-block d-sm-flex" }}@endif">
    <a href="{{ route("statystyki") }}" class="text-decoration-none">
        <div class="card shadow p-3 bg-white rounded h-100 w-100">
            <div class="card-body">
                <span class="btn btn-info disabled mb-3">
                    <i class="bi bi-bar-chart"></i>
                </span>
                <h5 class="card-title mb-0 text-muted">Statystyki</h5>
                <div class="card-text card-features-desc text-muted lh-sm">Dla każdej nieruchomści i najmu dostępne są statystyki zarobków.</div>
                
                <div class="text-end mt-3 card-features-readmore">
                    <span class="more-info">dowiedz się więcej &raquo;</span>
                </div>
            </div>
        </div>
    </a>
</div>
<div class="col-12 col-sm-6 col-lg-4 mb-3 align-items-stretch @if($current == "kontrola-rachunkow"){{ "d-none" }}@else{{ "d-block d-sm-flex" }}@endif">
    <a href="{{ route("kontrola_rachunkow") }}" class="text-decoration-none">
        <div class="card shadow p-3 bg-white rounded h-100 w-100">
            <div class="card-body">
                <span class="btn btn-dark disabled mb-3">
                    <i class="bi bi-currency-dollar"></i>
                </span>
                <h5 class="card-title mb-0 text-muted">Kontrola rachunków</h5>
                <div class="card-text card-features-desc text-muted lh-sm">Dodawaj rachunki dzięki temu nie przeoczysz żadnej płatności.</div>
                    
                <div class="text-end mt-3 card-features-readmore">
                    <span class="more-info">dowiedz się więcej &raquo;</span>
                </div>
            </div>
        </div>
    </a>
</div>
<div class="col-12 col-sm-6 col-lg-4 mb-3 align-items-stretch @if($current == "rozwiazania-chmurowe"){{ "d-none" }}@else{{ "d-block d-sm-flex" }}@endif">
    <a href="{{ route("rozwiazania_chmurowe") }}" class="text-decoration-none">
        <div class="card shadow p-3 bg-white rounded h-100 w-100">
            <div class="card-body">
                <span class="btn btn-success disabled mb-3">
                    <i class="bi bi-globe-americas"></i>
                </span>
                <h5 class="card-title mb-0 text-muted">Rozwiązanie chmurowe</h5>
                <div class="card-text card-features-desc text-muted lh-sm">Dzięki przechowywaniu danych w chmurze, dane dostępne są z każdego miejsca na ziemi.</div>
                    
                <div class="text-end mt-3 card-features-readmore">
                    <span class="more-info">dowiedz się więcej &raquo;</span>
                </div>
            </div>
        </div>
    </a>
</div>