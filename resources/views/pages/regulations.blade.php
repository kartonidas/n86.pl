@extends("page")

@section("metatitle")
    Regulamin świadczenia usług
@endsection

@section("content")
    <main class="container mt-5 mb-5 regulations">
        <h4 class="text-center mb-5">Regulamin świadczenia usług</h4>

        <div>
            <p>
                Serwis internetowy dostępny pod adresem {{ Request::root() }} prowadzony przez Artura Paturę wykonującego działalność
                gospodarczą pod firmą Artur Patura – netextend.pl pod adresem ul. Łódzka 46 lok. 48, 97-300 Piotrków Trybunalski, NIP:
                7712671332, REGON: 101425394, wpisanego do rejestru przedsiębiorców Centralnej Ewidencji i Informacji o Działalności
                Gospodarczej powadzonej przez ministra właściwego do spraw gospodarki.
            </p>
            <p>
                Dane kontaktowe Usługodawcy:<br/>
                <ol type="a">
                    <li>numer telefonu: +48 723 310 782,</li>
                    <li>adres e-mail: support@ninjatask.eu.</li>
                </ol>
            </p>
            <p>
                Niniejszy Regulamin określa zasady korzystania z Serwisu, rodzaje usług elektronicznych świadczonych za pośrednictwem
                Serwisu oraz prawa i obowiązki Usługobiorców oraz Usługodawcy. Zapoznanie się z niniejszym Regulaminem stanowi
                obowiązek każdego Usługobiorcy Serwisu.
            </p>
        </div>
        
        <div>
            <h5 class="text-center mt-5">§1. Definicje</h5>
            <ol>
                <li>
                    <strong>Serwis</strong> - serwis internetowy działający pod adresem {{ Request::root() }}.
                </li>
                <li>
                    <strong>Usługodawca</strong> – Artur Patura wykonujący działalność gospodarczą pod firmą Artur Patura – netextend.pl pod adresem ul.
                    Łódzka 46 lok. 48, 97-300 Piotrków Trybunalski, NIP: 7712671332, REGON: 101425394.
                </li>
                <li>
                    <strong>Usługobiorca</strong> - osoba fizyczna, osoba prawna lub jednostka organizacyjna nieposiadająca osobowości prawnej, której
                    prawo przyznaje zdolność prawną, która korzysta usług świadczonych przez Usługodawcę
                </li>
                <li>
                    <strong>Administrator konta</strong> – Usługobiorca rejestrujący i zarządzający Kontem usługobiorcy w Serwisie.
                </li>
                <li>
                    <strong>Użytkownik</strong> – Usługobiorca uprawniony do dostępu do Konta usługobiorcy przez Administratora konta.
                </li>
                <li>
                    <strong>Usługa</strong> – usługa świadczona drogą elektroniczną przez Usługodawcę na rzecz Usługobiorcy za pośrednictwem Serwisu.
                </li>
                <li>
                    <strong>Konto usługobiorcy</strong> - zbiór zasobów w systemie teleinformatycznym Usługodawcy, w którym gromadzone są informacje
                    na temat Usługobiorcy, w tym lista projektów i zadań oraz informacje adresowe oraz historia zamówień.
                </li>
            </ol>
        </div>
            
        <div>
            <h5 class="text-center mt-5">§2. Postanowienia ogólne</h5>
            <ol>
                <li>
                    Usługodawca zobowiązuje się do świadczenia usług na rzecz Usługobiorcy w zakresie i na warunkach określonych
                    w Regulaminie.
                </li>
                <li>
                    Usługobiorca zobowiązuje się do korzystania z Serwisu zgodnie z zasadami określonymi w Regulaminie, obowiązującymi
                    przepisami prawa i zasadami współżycia społecznego.
                </li>
                <li>
                    Korzystanie z Serwisu oraz Usług oznacza akceptację przez Usługobiorcę warunków określonych w Regulaminie oraz
                    Polityce prywatności.
                </li>
                <li>
                    Usługodawca przestrzega zasady ochrony danych osobowych Usługobiorców przewidziane rozporządzeniem Parlamentu
                    Europejskiego i Rady (UE) 2016/679 z dn. 27.04.2016 r. w sprawie ochrony osób fizycznych w związku z przetwarzaniem
                    danych osobowych i w sprawie swobodnego przepływu takich danych oraz uchylenia dyrektywy 95/46/WE. Usługobiorca
                    wyraża zgodę na gromadzenie, przechowywania i przetwarzanie przez Usługodawcę danych osobowych w celu realizacji
                    usługi. Szczegółowe zasady przetwarzania danych osobowych Usługobiorców określa Polityka prywatności Serwisu.
                    Usługodawca może wykorzystać dane Usługobiorcy w celu marketingu usług jedynie za wyraźną zgodą Usługobiorcy lub
                    w oparciu o właściwe przepisy prawa.
                </li>
                <li>
                    Zakazane jest dostarczanie przez Usługobiorcę treści o charakterze bezprawnym lub obraźliwym. Usługobiorca
                    zobowiązany jest do korzystania ze stron internetowych Usługodawcy w sposób niezakłócający ich funkcjonowania, w
                    szczególności poprzez nieużywanie określonego oprogramowania (w tym oprogramowania złośliwego) lub urządzeń.
                </li>
                <li>
                    Szczególne zagrożenia związane z korzystaniem z usług drogą elektroniczną to możliwość uzyskania przez nieuprawnione
                    osoby dostępu do danych transmitowanych przez sieć lub przechowywanych na dołączonych do sieci komputerach i
                    ingerencji w te dane, co może spowodować w szczególności, ich utratę, nieuprawnioną zmianę lub uniemożliwienie
                    korzystania z usług oferowanych z wykorzystaniem stron internetowych.
                </li>
                <li>
                    Postanowienia Regulaminu dotyczące konsumentów stosuje się odpowiednio do osoby fizycznej zawierającej umowę
                    bezpośrednio związaną z jej działalnością gospodarczą, gdy z treści tej umowy wynika, że nie posiada ona dla tej osoby
                    charakteru zawodowego, wynikającego w szczególności z przedmiotu wykonywanej przez nią działalności gospodarczej,
                    udostępnionego na podstawie przepisów o Centralnej Ewidencji i Informacji o Działalności Gospodarczej.
                </li>
            </ol>
        </div>
            
        <div>
            <h5 class="text-center mt-5">§3. Ogólne warunki świadczenia usług</h5>
            
            <ol>
                <li>
                    Usługodawca świadczy za pośrednictwem Serwisu usługi polegające w szczególności na umożliwieniu Usługobiorcy
                    dostępu do Serwisu, w tym do informacji zamieszczonych w Serwisie oraz do wyświetlania ich na urządzeniu końcowym
                    Użytkownika, a także polegające na umożliwieniu rejestracji w celu utworzenia Konta usługobiorcy oraz utrzymaniu Konta
                    w Serwisie celem korzystania przez Usługobiorcę z funkcjonalności Serwisu.
                </li>
                <li>
                    Usługodawca świadczy usługi bezpłatne lub odpłatnie na zasadach określonych Regulaminem.
                </li>
                <li>
                    Informacje znajdujące się na stronie Serwisu stanowią zaproszenie do zawarcia umowy w rozumieniu art. 71 ustawy z dnia
                    23 kwietnia 1964 r. Kodeks cywilny.
                </li>
                <li>
                    Wszelkie ceny podane na stronie Serwisu podawane są w polskich złotych (PLN) oraz są cenami brutto.
                </li>
                <li>
                    Usługodawca zastrzega sobie prawo do dokonywania zmian w cenach, wprowadzania nowych usług jak i też
                    przeprowadzania i odwoływania akcji promocyjnych bądź dokonywania zmian w trwających już promocjach. Zmiany, o
                    których mowa w zdaniu poprzednim nie mają zastosowania do umów zawartych przed wprowadzeniem przez
                    Usługodawcę zmian. W przypadku akcji promocyjnych obejmujących czasową obniżkę ceny Usługodawca na stronie
                    wskazuje, obok ceny promocyjnej, poprzednią najniższą cenę, jaka obowiązywała w okresie 30 dni przed wprowadzeniem
                    promocji. Skala obniżki będzie ustalana w stosunku do najniższej wskazanej ceny.
                </li>
                <li>
                    Wymagania techniczne niezbędne do korzystania z usług świadczonych przez Usługodawcę:
                    <ol type="a">
                        <li>urządzenie z dostępem do sieci Internet,</li>
                        <li>przeglądarka internetowa obsługująca pliki Cookies,</li>
                        <li>dostęp do poczty elektronicznej.</li>
                    </ol>
                </li>
                <li>
                    Usługobiorca ponosi opłaty związane z dostępem do sieci Internet i transmisją danych zgodnie z taryfą swojego dostawcy
                    usług internetowych.
                </li>
            </ol>
        </div>
            
        <div>
            <h5 class="text-center mt-5">§4. Rejestracja</h5>
            <ol>
                <li>
                    Rejestracja dokonywana jest przez Usługobiorcę poprzez poprawne wypełnienie formularza rejestracji dostępnego w
                    Serwisie.
                </li>
                <li>
                    Usługobiorca wypełniając Formularz rejestracji zobowiązany jest do podania wyłącznie informacji prawdziwych.
                </li>
                <li>
                    Dokonując rejestracji Usługobiorca potwierdza zapoznanie się z treścią niniejszego Regulaminu oraz akceptację jego
                    postanowień.
                </li>
                <li>
                    Usługobiorca może posiadać wyłącznie jedno Konto usługobiorcy.
                </li>
                <li>
                    Usługobiorcy zabrania się udostępniania Konta usługobiorcy osobom trzecim (z zastrzeżeniem §4 pkt 10 Regulaminu).
                </li>
                <li>
                    Usługobiorca nie posiada prawa do zbywania Konta usługobiorcy innym podmiotom.
                </li>
                <li>
                    Usługobiorca ponosi wyłączną odpowiedzialność związaną z ujawnieniem innym osobom danych umożlwiających
                    logowanie w Serwisie.
                </li>
                <li>
                    W przypadku notorycznego naruszania postanowień niniejszego Regulaminu Usługodawca zastrzega sobie prawo do
                    zablokowania bądź całkowitego usunięcia Konta usługobiorcy. Uprawnienia, o których mowa w zdaniu poprzednim,
                    Usługodawca wykonuje poprzednim wezwaniu Usługobiorcy do zaprzestania naruszeń i poinformowaniu Usługobiorcy o
                    skutkach niezastosowania się do wezwania.
                </li>
                <li>
                    Usługobiorca rejestrujący Konto w Serwisie automatycznie uzyskuje rangę Administratora konta.
                </li>
                <li>
                    Administrator konta posiada uprawnienie do utworzenia dostępu dla Użytkowników. Dodając nowego Użytkownika
                    Administrator konta wskazuje poziom uprawnień Użytkownika do działań w obrębie Konta usługobiorcy.
                </li>
                <li>
                    Administrator konta odpowiada za działania Użytkowników w Serwisie jak za działania własne.
                </li>
                <li>
                    Administrator konta może korzystać z funkcjonalności Serwisu w dostępie bezpłatnym lub w dostępie płatnym.
                </li>
            </ol>
        </div>
            
        <div>
            <h5 class="text-center mt-5">§5. Dostęp bezpłatny</h5>
            <ol>
                <li>
                    Z funkcjonalności Serwisu w ramach dostępu bezpłatnego może korzystać każdy zarejestrowany Administrator konta.
                </li>
                <li>
                    W ramach dostępu bezpłatnego Usługodawca umożliwia:
                    <ol type="a">
                        <li>dostęp do Konta usługobiorcy poprzez aplikację mobilną dla systemów Android,</li>
                        <li>dodawanie przez Administratora konta nieograniczonej liczby Użytkowników,</li>
                        <li>utworzenie i prowadzenie w jednym czasie nie więcej niż dwóch projektów,</li>
                        <li>utworzenie nie więcej niż 10 zadań do projektów,</li>
                        <li>przechowywanie plików w łącznym rozmiarze nie większym niż 10 MB.</li>
                    </ol>
                </li>
                <li>
                    Usługodawca zastrzega sobie w każdym czasie prawo do ograniczania lub poszerzania funkcjonalności dostępnych w
                    Serwisie bezpłatnie.
                </li>
            </ol>
        </div>
            
        <div>
            <h5 class="text-center mt-5">§6. Dostęp płatny</h5>
            <ol>
                <li>
                    Dostęp płatny aktywowany jest dla Administratora konta po dokonaniu przez niego płatności za usługę zgodnie z
                    aktualnym cennikiem dostępnym w Serwisie.
                </li>
                <li>
                    Dostęp płatny przyznawany jest na okres jednego miesiąca lub jednego roku (zgodnie z wyborem Administratora konta)
                    liczony od momentu aktywacji.
                </li>
                <li>
                    Aktywacja dostępu płatnego następuje niezwłocznie w momencie otrzymania płatności przez Usługodawcę (zgodnie z §8
                    pkt 2 Regulaminu).
                </li>
                <li>
                    W ramach dostępu płatnego Usługodawca umożliwia:
                    <ol type="a">
                        <li>dostęp do Konta usługobiorcy poprzez aplikację mobilną dla systemów Android,</li>
                        <li>dodawanie przez Administratora konta nieograniczonej liczby Użytkowników,</li>
                        <li>utworzenie i prowadzenie nieograniczonej liczby projektów,</li>
                        <li>utworzenie nieograniczonej liczby zadań do projektów,</li>
                        <li>przechowywanie plików w łącznym rozmiarze nie większym niż 200 MB,</li>
                        <!--<li>korzystanie z menadżera plików.</li>-->
                    </ol>
                </li>
                <li>
                    Usługodawca zastrzega sobie w każdym czasie prawo do ograniczania lub poszerzania funkcjonalności objętych
                    dostępem płatnym z zastrzeżeniem, iż zmiany wprowadzone przez Usługodawcę nie będą obowiązywać Administratora
                    konta z aktywnym dostępem płatnym w momencie wprowadzenia zmian.
                </li>
                <li>
                    Administrator konta zainteresowany korzystaniem z dostępu płatnego po upływie okresu wskazanego w pkt 2 zobowiązany
                    jest do ponownego zamówienia usługi i wniesienia odpłatności zgodnie z cennikiem aktualnym na dzień ponownego
                    zamówienia.
                </li>
                <li>
                    Zastosowanie przez Usługodawcę sankcji, o której mowa w §4 pkt 8 skutkować będzie utratą dostępu płatnego bez prawa
                    Administratora konta do żądania zwrotu płatności wniesionej za usługę.
                </li>
            </ol>
        </div>
            
        <div>
            <h5 class="text-center mt-5">§7. Treści zakazane</h5>
            <ol>
                <li>
                    Usługobiorca korzystający z funkcjonalności Serwisu w żadnym wypadku nie może zamieszczać w nim lub
                    rozpowszechniać za jego pomocą treści:
                    <ol type="a">
                        <li>
                            nawołujących do popełnienia czynu zabronionego lub pochwala popełnienie takiego czynu,
                        </li>
                        <li>
                            obrażających uczucia religijne innych osób poprzez znieważenie przedmiotu czci religijnej lub miejsca służącego do
                            celów religijnych,
                        </li>
                        <li>
                            pomawiających inną osobę, grupy osób, instytucje, osoby prawne lub inne podmioty o takie postępowanie lub
                            właściwości, które mogą poniżyć je w opinii publicznej lub narazić na utratę zaufania potrzebnego do wykonywania
                            danego zawodu, działalności czy zajmowania określonego stanowiska,
                        </li>
                        <li>
                            propagujących lub nawołujących do nienawiści na tle rasowym, narodowościowym, etnicznym, wyznaniowym albo
                            seksualnym,
                        </li>
                        <li>
                            naruszających prawa autorskie i/lub prawa pokrewne osób trzecich,
                        </li>
                        <li>
                            mających charakter bezprawny,
                        </li>
                        <li>
                            posiadających odnośniki do innych stron internetowych zawierających złośliwe oprogramowanie lub stron
                            internetowych służących do wyłudzania danych.
                        </li>
                    </ol>
                </li>
                <li>
                    Usługobiorca zamieszczając jakiekolwiek treści w ramach Serwisu potwierdza tym samym, iż posiada prawa autorskie,
                    prawa pokrewne lub inne ważne uprawnienia do korzystania z takich treści. W przypadku zgłoszenia wobec treści przez
                    osoby trzecie roszczeń, Usługobiorca przyjmuje takie roszczenia na siebie i zwalnia Usługodawcę z wszelkiej
                    odpowiedzialności.
                </li>
                <li>
                    Usługodawca zastrzega sobie prawo do weryfikacji treści zamieszczanych przez Usługobiorcę w ramach Serwisu, co
                    może skutkować jej usunięciem w przypadku, gdy zamieszczona przez Usługobiorcę treść narusza postanowienia
                    niniejszego Regulaminu.
                </li>
            </ol>
        </div>
            
        <div>
            <h5 class="text-center mt-5">§8. Warunki płatności</h5>
            <ol>
                <li>
                    Usługobiorca reguluje płatności na rzecz Usługodawcy za pośrednictwem systemu płatności elektronicznych Paynow
                    zgodnie z odstępnymi kanałami płatności, którego operatorem jest mBank S.A. z siedzibą w Warszawie (Klient dokonuje
                    płatności zgodnie z regulaminem systemu płatności Paynow).
                </li>
                <li>
                    Za moment dokonania płatności uznaje się poprawną autoryzację transakcji przez operatora systemu płatności
                    elektronicznych.
                </li>
                <li>
                    Dowód sprzedaży przesyłany jest na adres e-mail wskazany na Koncie usługobiorcy oraz dostępny jest do pobrania po
                    zalogowaniu się na Koncie usługobiorcy.
                </li>
                <li>
                    W przypadku wystąpienia konieczność zwrotu środków za transakcję dokonaną przez Usługobiorcę kartą płatniczą, zwrot
                    dokonywany jest na rachunek bankowy przypisany do karty płatniczej Usługobiorcy.
                </li>
            </ol>
        </div>
            
        <div>
            <h5 class="text-center mt-5">§9. Odpowiedzialność Usługodawcy</h5>
            <ol>
                <li>
                    Usługodawca informuje, iż zgodnie z art. 15 ustawy z dnia 18 lipca 2002 r. o świadczeniu usług drogą elektroniczną nie
                    jest zobowiązany do sprawdzania przekazywanych, przechowywanych lub udostępnianych przez niego danych, o których
                    mowa w art. 12-14 wskazanej ustawy o ile dane te nie pochodzą od niego.
                </li>
                <li>
                    Usługodawca nie ponosi odpowiedzialności za wprowadzenie przez Usługobiorcy błędnych danych (w szczególności przez
                    podanie błędnych danych w formularzach dostępnych na stronie), przekazanie niepełnych lub wadliwych materiałów lub
                    działanie Usługobiorcy w sposób utrudniający lub uniemożliwiający świadczenie i realizację usług przez Usługodawcę.
                </li>
                <li>
                    Usługodawca nie ponosi odpowiedzialności za skutki korzystania z Serwisu przez Usługobiorcę w sposób niezgodny z
                    postanowieniami Regulaminu, obowiązującymi przepisami prawa oraz obowiązującymi w tym zakresie zasadami
                    współżycia społecznego lub zwyczajami.
                </li>
                <li>
                    Usługodawca zastrzega sobie prawo do zawieszenia lub zakończenia świadczenia poszczególnych funkcjonalności
                    Serwisu internetowego z uwagi na konieczność konserwacji, przeglądu lub rozbudowy bazy technicznej bądź
                    oprogramowania. Zawieszenie bądź zakończenie świadczeń poszczególnych funkcjonalności Serwisu nie może naruszać
                    praw Usługobiorcy.
                </li>
                <li>
                    Ryzyko związane z używaniem, użytkowaniem, posiadaniem i wykorzystaniem informacji udostępnianych na stronach
                    Serwisu ponosi Usługobiorca. Usługodawca nie ponosi żadnej odpowiedzialności wobec Usługobiorcy lub osób trzecich z
                    tytułu szkód, zarówno bezpośrednich, jak i pośrednich, związanych z wykorzystaniem informacji udostępnianych na
                    stronach Serwisu.
                </li>
                <li>
                    W przypadku szkody poniesionej przez Usługobiorcę, a wynikającej z umyślnego działania Usługodawcy, Usługodawca
                    odpowiada jedynie za rzeczywiste szkody poniesione przez Usługobiorcę z zastrzeżeniem, iż odpowiedzialność
                    Usługodawcy zostaje ograniczona do kwoty płatności uiszczonej przez Usługobiorcę za usługę płatną świadczoną w
                    Serwisie.
                </li>
            </ol>
        </div>
            
        <div>
            <h5 class="text-center mt-5">§10. Odpowiedzialność za zgodność treści cyfrowej z umową</h5>
            <ol>
                <li>
                    Usługodawca ponosi odpowiedzialność za brak zgodności z umową treści cyfrowej lub usługi cyfrowej dostarczanych
                    jednorazowo lub w częściach, który istniał w chwili ich dostarczenia i ujawnił się w ciągu dwóch lat od tej chwili.
                    Domniemywa się, że brak zgodności treści cyfrowej lub usługi cyfrowej z umową, który ujawnił się przed upływem roku od
                    chwili dostarczenia treści cyfrowej lub usługi cyfrowej, istniał w chwili ich dostarczenia.
                </li>
                <li>
                    Usługodawca ponosi odpowiedzialność za brak zgodności z umową treści cyfrowej lub usługi cyfrowej dostarczanych w
                    sposób ciągły, który wystąpił lub ujawnił się w czasie, w którym zgodnie z umową miały być dostarczane. Domniemywa
                    się, że brak zgodności treści cyfrowej lub usługi cyfrowej z umową wystąpił w tym czasie, jeżeli w tym czasie się ujawnił.
                </li>
                <li>
                    Usługodawca nie ponosi odpowiedzialności za brak zgodności z umową treści cyfrowej lub usługi cyfrowej, jeżeli:
                    <ol type="a">
                        <li>
                            środowisko cyfrowe konsumenta nie jest kompatybilne z wymaganiami technicznymi, o których Usługodawca
                            poinformował go w sposób jasny i zrozumiały przed zawarciem umowy;
                        </li>
                        <li>
                            konsument, poinformowany w jasny i zrozumiały sposób przed zawarciem umowy o obowiązku współpracy ze
                            Usługodawcą, w rozsądnym zakresie i przy zastosowaniu najmniej uciążliwych dla siebie środków technicznych, w celu
                            ustalenia czy brak zgodności treści cyfrowej lub usługi cyfrowej z umową w odpowiednim czasie wynika z cech
                            środowiska cyfrowego konsumenta, nie wykonuje tego obowiązku.
                        </li>
                    </ol>
                </li>
                <li>
                    Jeżeli treść cyfrowa lub usługa cyfrowa są niezgodne z umową, konsument może żądać doprowadzenia do ich zgodności
                    z umową. Usługodawca może odmówić doprowadzenia treści cyfrowej lub usługi cyfrowej do zgodności z umową, jeżeli
                    doprowadzenie do zgodności treści cyfrowej lub usługi cyfrowej z umową jest niemożliwe albo wymagałoby nadmiernych
                    kosztów dla Usługodawcy.
                </li>
                <li>
                    Usługodawca doprowadza treść cyfrową lub usługę cyfrową do zgodności z umową w rozsądnym czasie od chwili, w której
                    został poinformowany przez konsumenta o braku zgodności z umową, i bez nadmiernych niedogodności dla konsumenta,
                    uwzględniając ich charakter oraz cel, w jakim są wykorzystywane. Koszty doprowadzenia treści cyfrowej lub usługi
                    cyfrowej do zgodności z umową ponosi Usługodawca.
                </li>
                <li>
                    Jeżeli treść cyfrowa lub usługa cyfrowa są niezgodne z umową, konsument może złożyć oświadczenie o obniżeniu ceny
                    albo odstąpieniu od umowy, gdy:
                    <ol type="a">
                        <li>
                            doprowadzenie do zgodności treści cyfrowej lub usługi cyfrowej z umową jest niemożliwe albo wymaga nadmiernych
                            kosztów;
                        </li>
                        <li>
                            Usługodawca nie doprowadził treści cyfrowej lub usługi cyfrowej do zgodności z umową;
                        </li>
                        <li>
                            brak zgodności treści cyfrowej lub usługi cyfrowej z umową występuje nadal, mimo że Usługodawca próbował
                            doprowadzić treść cyfrową lub usługę cyfrową do zgodności z umową;
                        </li>
                        <li>
                            brak zgodności treści cyfrowej lub usługi cyfrowej z umową jest na tyle istotny, że uzasadnia obniżenie ceny albo
                            odstąpienie od umowy bez uprzedniego skorzystania przez konsumenta z żądania doprowadzenia przez Usługodawcę
                            treści cyfrowej lub usługi cyfrowej do zgodności z umową;
                        </li>
                        <li>
                            z oświadczenia Usługodawcy lub okoliczności wyraźnie wynika, że nie doprowadzi on treści cyfrowej lub usługi
                            cyfrowej do zgodności z umową w rozsądnym czasie lub bez nadmiernych niedogodności dla konsumenta.
                        </li>
                    </ol>
                </li>
                <li>
                    Konsument nie może odstąpić od umowy, jeżeli treść cyfrowa lub usługa cyfrowa są dostarczane w zamian za zapłatę
                    ceny, a brak zgodności treści cyfrowej lub usługi cyfrowej z umową jest nieistotny.
                </li>
                <li>
                    Usługodawca może dokonać zmiany treści cyfrowej lub usługi cyfrowej, która nie jest niezbędna do zachowania jej
                    zgodności z umową, tylko jeżeli umowa tak stanowi i jedynie z uzasadnionych przyczyn w tej umowie wskazanych.
                    Usługodawca nie może jednak dokonać zmiany treści cyfrowej lub usługi cyfrowej dostarczanych w sposób jednorazowy.
                    Wprowadzenie zmiany, o której mowa w zdaniu poprzednim nie może wiązać się z jakimikolwiek kosztami po stronie
                    konsumenta. Usługodawca ma obowiązek poinformować konsumenta w sposób jasny i zrozumiały o dokonywanej
                    zmianie.
                </li>
                <li>
                    Jeżeli zmiana, o której mowa w pkt 8, istotnie i negatywnie wpływa na dostęp konsumenta do treści cyfrowej lub usługi
                    cyfrowej lub korzystanie z nich, Usługodawca jest zobowiązany poinformować konsumenta z odpowiednim wyprzedzeniem
                    na trwałym nośniku o właściwościach i terminie dokonania tej zmiany oraz prawie do wypowiedzenia umowy bez
                    zachowania terminu wypowiedzenia w ciągu 30 dni od dnia dokonania zmiany lub poinformowania o tej zmianie, jeżeli
                    poinformowanie nastąpiło później niż ta zmiana. Konsument nie posiada prawa do wypowiedzenia umowy, jeżeli
                    Usługodawca zapewnił konsumentowi uprawnienie do zachowania, bez dodatkowych kosztów, treści cyfrowej lub usługi
                    cyfrowej zgodnych z umową, w stanie niezmienionym.
                </li>
            </ol>
        </div>
            
        <div>
            <h5 class="text-center mt-5">§11. Odstąpienie od umowy o świadczenie usług drogą elektroniczną</h5>
            <ol>
                <li>
                    Usługobiorcy przysługuje prawo do odstąpienia od umowy o świadczenie usług drogą elektroniczną o charakterze ciągłym
                    i bezterminowym. Usługobiorca może rozwiązać umowę w każdym czasie za wypowiedzeniem w trybie natychmiastowym i
                    bez podawania przyczyny wysyłając stosowne oświadczenie na adres poczty elektronicznej Usługodawcy.
                </li>
                <li>
                    Po otrzymaniu oświadczenia o odstąpieniu od umowy w postaci udostępnienia Konta usługobiorcy, Usługodawca usuwa
                    Konta z Serwisu. Usunięcie Konta może skutkować zaprzestaniem świadczenia przez Usługodawcę usług odpłatnych bez
                    przyznania prawa Usługobiorcy do zwrotu uiszczonej płatności.
                </li>
                <li>
                    Usługodawca zastrzega sobie prawo do wypowiedzenia umowy o świadczenie usług elektronicznych, w tym do usunięcia
                    Konta za okresem wypowiedzenia wynoszącym 14 dni w przypadku niestosowania się przez Usługobiorcę do postanowień
                    niniejszego Regulaminu.
                </li>
                <li>
                    Usługodawca zastrzega sobie prawo do wypowiedzenia umowy o świadczenie usług elektronicznych, w tym usunięcia
                    Konta w przypadku, gdy od ostatniej aktywności na Koncie minęło co najmniej 6 miesięcy lub gdy od dnia rejestracji w
                    Serwisie przez okres 3 miesięcy Usługobiorca nie podjął żadnej czynności związanej z usługami świadczonymi za
                    pośrednictwem Serwisu i nie posiada aktywnego dostępu płatnego.
                </li>
            </ol>
        </div>
            
        <div>
            <h5 class="text-center mt-5">§12. Ustawowe prawo do odstąpienia od umowy</h5>
            <ol>
                <li>
                    Usługobiorcy będącemu konsumentem przysługuje prawo do odstąpienia od umowy zawartej na odległość lub poza
                    lokalem przedsiębiorstwa Usługodawcy w terminie 14 dni od dnia, w którym zawarto umowę. Do zachowania terminu
                    wystarczy przesłanie oświadczenia na adres siedziby lub adres poczty elektronicznej Usługodawcy.
                </li>
                <li>
                    W razie odstąpienia od umowy, jest ona uważana za niezawartą a konsument jest zwolniony od wszelkich zobowiązań.
                    Płatności dokonane przez konsumenta zostaną zwrócone przez Usługodawcę w takie samej formie w jakiej konsument
                    dokonał zapłaty, chyba, że konsument wyrazi zgodę na zwrot płatności w inny sposób, który nie spowoduje dla niego
                    dodatkowych obciążeń. Zwrot następuje w terminie 14 dni od dnia odstąpienia od umowy.
                </li>
                <li>
                    W związku z art. 38 ustawy dnia 30 maja 2014 r. o prawach konsumenta, prawo do odstąpienia nie przysługuje m. in. w
                    odniesieniu do umów o świadczenie usług, jeżeli Usługodawca wykonał w pełni usługę (np. aktywował dostęp płatny) za
                    wyraźną zgodą konsumenta, który został poinformowany przed rozpoczęciem świadczenia, że po spełnieniu świadczenia
                    przez Usługodawcę utraci prawo do odstąpienia od umowy.
                </li>
            </ol>
        </div>
            
        <div>
            <h5 class="text-center mt-5">§13. Postępowanie reklamacyjne</h5>
            <ol>
                <li>
                    W przypadku niewykonania lub nienależytego wykonania przez Usługodawcę usług świadczonych za pomocą Serwisu,
                    Usługobiorca jest uprawniony do złożenia reklamacji drogą elektroniczną na adres e-mail Usługodawcy.
                </li>
                <li>
                    Prawidłowo złożona reklamacja powinna zawierać oznaczenie Usługobiorcy (nazwa firmy, imię i nazwisko oraz adres
                    poczty elektronicznej), przedmiot reklamacji wraz ze wskazaniem okresu, którego dotyczy reklamacja oraz okoliczności
                    uzasadniające złożenie reklamacji. W przypadku podania niepełnych danych Usługodawca wezwie Usługobiorcę do
                    uzupełnienia danych.
                </li>
                <li>
                    Reklamację rozpatruje Usługodawca w terminie do 30 dni od dnia otrzymania reklamacji lub jej uzupełnienia przez
                    Usługobiorcę.
                </li>
                <li>
                    Nierozpatrzenie reklamacji w terminie zakreślonym w pkt 3 uznaje się za jej uwzględnienie.
                </li>
            </ol>
        </div>
            
        <div>
            <h5 class="text-center mt-5">§14. Własność intelektualna</h5>
            <ol>
                <li>
                    Wszelkie treści zamieszczone na stronach internetowych Usługodawcy (wliczając w to grafikę, teksty, układ stron
                    i logotypy), a nie pochodzące od innych dostawców, korzystają z ochrony przewidzianej dla praw autorskich i są wyłączną
                    własnością Usługodawcy. Wykorzystywanie tych treści bez pisemnej zgody Usługodawcy skutkuje odpowiedzialnością
                    cywilną oraz karną.
                </li>
                <li>
                    Usługobiorca zobowiązany jest do korzystania z wszelkich treści zamieszczonych w ramach stron internetowych
                    Usługodawcy jedynie w zakresie własnego użytku osobistego. Wykorzystanie treści w innym zakresie jest dopuszczone
                    wyłącznie w przypadku, gdy tak wskazane zostało wyraźnie przez Usługodawcę.
                </li>
                <li>
                    Korzystanie ze stron internetowych Usługodawcy, w tym korzystanie z materiałów tekstowych, graficznych, zdjęć, aplikacji,
                    baz danych czy innych treści, nie oznacza nabycia przez Usługobiorcę jakichkolwiek praw w odniesieniu do wskazanych
                    treści, a w szczególności nie oznacza nabycia praw autorskich majątkowych, praw pokrewnych lub licencji.
                </li>
                <li>
                    Zabronione jest podejmowanie następujących czynności bez wyraźnej zgody Usługodawcy:
                    <ol type="a">
                        <li>
                            kopiowanie, modyfikowanie oraz transmitowanie elektronicznie lub w inny sposób Serwisu lub jego części, a także
                            poszczególnych treści udostępnianych za jego pomocą,
                        </li>
                        <li>
                            rozpowszechnianie w jakikolwiek sposób publikowanych w Serwisie treści,
                        </li>
                        <li>
                            pobieranie zawartości baz danych i wtórne jej wykorzystanie w całości lub części.
                        </li>
                    </ol>
                </li>
            </ol>
        </div>
            
        <div>
            <h5 class="text-center mt-5">§15. Postanowienia końcowe</h5>
            <ol>
                <li>
                    Usługodawca zastrzega sobie prawo do zmiany niniejszego Regulaminu. O zmianie Regulaminu Usługodawca powiadomi
                    na stronie Serwisu internetowego na co najmniej 14 dni kalendarzowych przed wejściem w życie zmian w Regulaminie.
                    Zmiana postanowień Regulaminu nie ma zastosowania do Usługobiorców, którzy złożyli zamówienie w czasie
                    obowiązywania poprzedniej wersji Regulaminu. Zmiana Regulaminu w czasie trwania stosunku umownego o charakterze
                    ciągłym wiąże drugą stronę, jeżeli zostały zachowane wymagania określone w art. 384 Kodeksu cywilnego, a strona nie
                    wypowiedziała umowy w terminie wypowiedzenia wynoszącym 14 dni kalendarzowych.
                </li>
                <li>
                    Usługodawca zachowuje sobie prawo do okresowego wyłączania dostępu do Serwisu lub wybranych funkcjonalności
                    Serwisu w przypadku, gdy jest to niezbędne w celu rozbudowy lub konserwacji zasobów technicznych lub
                    teleinformatycznych Usługodawcy związanych z działaniem Serwisu.
                </li>
                <li>
                    W pozostałych kwestiach nieuregulowanych zapisami niniejszego Regulaminu mają zastosowanie odpowiednie przepisy
                    prawa polskiego.
                </li>
                <li>
                    Spory powstałe w wyniku świadczenia usług na podstawie niniejszego Regulaminu zostaną poddane pod rozstrzygnięcie
                    sądowi powszechnemu według siedziby Usługodawcy, jeżeli właściwe przepisy nie stanowią inaczej.
                </li>
                <li>
                    Usługobiorca będący konsumentem ma prawo do skorzystania z pozasądowych metod rozstrzygania sporów
                    i dochodzenia roszczeń w drodze mediacji lub sądu polubownego. Niezależnie od tego konsument może zwrócić się o
                    pomoc do miejskiego (powiatowego) rzecznika konsumentów. Wszelkie niezbędne informacje można uzyskać na stronie
                    internetowej Urzędu Ochrony Konkurencji i Konsumentów pod adresem <a href="https://uokik.gov.pl/" target="_blank">www.uokik.gov.pl</a>. Usługobiorca będący
                    konsumentem może ponadto skorzystać z elektronicznego sposobu rozwiązywania sporów z Usługodawcą za
                    pośrednictwem platformy ODR dostępnej pod adresem <a href="http://ec.europa.eu/consumers/odr/" target="_blank">http://ec.europa.eu/consumers/odr/</a>.
                </li>
                <li>
                    Regulamin wchodzi w życie z dniem 7 lipca 2023 r.
                </li>
            </ol>
        </div>
    </main>
        
@endsection