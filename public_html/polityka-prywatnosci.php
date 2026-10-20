<?php
if (!defined('PD_APP')) { define('PD_APP', true); } // bezpośrednio (Apache) lub przez dispatcher w index.php (nginx)
$page = 'polityka';
$title = 'Polityka prywatności | Pomoc Drogowa Pabianice';
$desc = 'Polityka prywatności strony pomocdrogowa-pabianice.pl: administrator danych, cele i podstawy przetwarzania, odbiorcy, prawa użytkownika oraz pliki cookies.';
$path = '/polityka-prywatnosci/';
$preloadHero = false;
include __DIR__ . '/partials/config.php';
$ogImage = 'assets/img/hero/' . pd_slug('kontakt-hero') . '.jpg';
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/header.php';
?>
  <!-- HERO -->
  <section id="top" style="background:#141414;scroll-margin-top:var(--navh)">
    <div style="max-width:1200px;margin:0 auto;padding:clamp(40px,7vw,64px) 20px;display:flex;flex-direction:column;gap:14px">
      <nav aria-label="Okruszki" style="font-size:14px;color:#aaa"><a href="/" style="color:#aaa">Strona główna</a> <span style="color:#666">/</span> <span style="color:#f5c518">Polityka prywatności</span></nav>
      <div style="font-size:13px;letter-spacing:3px;font-weight:700;color:#f5c518">DANE OSOBOWE I COOKIES</div>
      <h1 style="margin:0;font-family:'Barlow Condensed';font-weight:800;font-size:clamp(40px,6vw,68px);line-height:.95">Polityka prywatności</h1>
    </div>
  </section>
  <div style="height:16px;background:repeating-linear-gradient(135deg,#f5c518 0 22px,#111 22px 44px)"></div>

  <!-- TREŚĆ -->
  <section class="pol" aria-label="Treść polityki prywatności">
    <p class="pol-date">Ostatnia aktualizacja: 21 października 2026.</p>

    <h2>1. Administrator danych</h2>
    <p>Administratorem danych osobowych jest Łukasz Rogowski prowadzący działalność gospodarczą pod firmą „POMOC DROGOWA Łukasz Rogowski”, ul. Stanisława Moniuszki 41 lok. 16, 95-200 Pabianice, NIP 7312064539, REGON 383037779.</p>
    <p>Kontakt z administratorem:</p>
    <ul>
      <li>telefon: <a href="<?php echo $PHONE_HREF; ?>"><?php echo $PHONE; ?></a>,</li>
      <li>pisemnie: na adres podany powyżej.</li>
    </ul>
    <p>Administrator nie wyznaczył inspektora ochrony danych.</p>

    <h2>2. Zakres polityki</h2>
    <p>Polityka dotyczy strony internetowej pomocdrogowa-pabianice.pl oraz kontaktu telefonicznego z numerem podanym na tej stronie.</p>

    <h2>3. Cele, podstawy prawne i okres przechowywania danych</h2>
    <p>Dane osobowe przetwarzamy w następujących celach:</p>

    <div class="pol-box">
      <h3>a) Odpowiedź na zapytanie telefoniczne</h3>
      <ul>
        <li><b>Podstawa prawna:</b> art. 6 ust. 1 lit. f RODO (uzasadniony interes administratora polegający na udzieleniu odpowiedzi); gdy zapytanie dotyczy przyjęcia zlecenia – art. 6 ust. 1 lit. b RODO.</li>
        <li><b>Okres przechowywania:</b> do zakończenia kontaktu, a następnie do upływu terminu przedawnienia roszczeń.</li>
      </ul>
    </div>

    <div class="pol-box">
      <h3>b) Realizacja zlecenia (pomoc drogowa, holowanie, transport pojazdu, auto zastępcze)</h3>
      <ul>
        <li><b>Zakres danych:</b> imię i nazwisko, numer telefonu, miejsce zdarzenia i miejsce docelowe, dane pojazdu (marka, model, numer rejestracyjny), a przy aucie zastępczym z OC sprawcy – także dane potrzebne do rozliczenia szkody z ubezpieczycielem.</li>
        <li><b>Podstawa prawna:</b> art. 6 ust. 1 lit. b RODO (podjęcie działań na żądanie osoby, której dane dotyczą, przed zawarciem umowy oraz wykonanie umowy).</li>
        <li><b>Okres przechowywania:</b> przez czas realizacji usługi, a następnie do upływu terminu przedawnienia roszczeń.</li>
      </ul>
      <p>Jeżeli zlecenie przekazał ubezpieczyciel lub firma assistance, dane otrzymujemy od tego podmiotu w zakresie niezbędnym do wykonania usługi.</p>
    </div>

    <div class="pol-box">
      <h3>c) Wypełnianie obowiązków podatkowych i rachunkowych</h3>
      <ul>
        <li><b>Podstawa prawna:</b> art. 6 ust. 1 lit. c RODO (obowiązek prawny ciążący na administratorze).</li>
        <li><b>Okres przechowywania:</b> do upływu okresu przedawnienia zobowiązań podatkowych – co do zasady 5 lat od końca roku kalendarzowego, w którym upłynął termin płatności podatku.</li>
      </ul>
    </div>

    <div class="pol-box">
      <h3>d) Ustalenie, dochodzenie lub obrona roszczeń</h3>
      <ul>
        <li><b>Podstawa prawna:</b> art. 6 ust. 1 lit. f RODO (uzasadniony interes administratora).</li>
        <li><b>Okres przechowywania:</b> do upływu terminu przedawnienia roszczeń.</li>
      </ul>
    </div>

    <div class="pol-box">
      <h3>e) Zapewnienie działania i bezpieczeństwa strony internetowej</h3>
      <ul>
        <li><b>Zakres danych:</b> logi serwera – adres IP, data i godzina odwiedzin, adres odwiedzanej podstrony, typ przeglądarki.</li>
        <li><b>Podstawa prawna:</b> art. 6 ust. 1 lit. f RODO (uzasadniony interes administratora polegający na zapewnieniu prawidłowego i bezpiecznego działania strony).</li>
        <li><b>Okres przechowywania:</b> przez czas niezbędny do realizacji tego celu.</li>
      </ul>
    </div>

    <div class="pol-box">
      <h3>f) Wyświetlenie mapy dojazdu i obszaru działania</h3>
      <ul>
        <li><b>Zakres danych:</b> adres IP i informacje o przeglądarce przekazywane OpenStreetMap Foundation przy pobieraniu fragmentów mapy (szczegóły w pkt 9).</li>
        <li><b>Podstawa prawna:</b> art. 6 ust. 1 lit. f RODO (uzasadniony interes administratora polegający na pokazaniu siedziby i obszaru działania).</li>
        <li><b>Okres przechowywania:</b> administrator nie zapisuje tych danych; OpenStreetMap Foundation przechowuje je zgodnie ze swoją polityką prywatności.</li>
      </ul>
    </div>

    <div class="pol-box">
      <h3>g) Zarządzanie zgodami na pliki cookies i wykazanie ich uzyskania (CookieYes)</h3>
      <ul>
        <li><b>Zakres danych:</b> identyfikator zgody, dokonany wybór, data i godzina, skrócony adres IP, informacje o przeglądarce.</li>
        <li><b>Podstawa prawna:</b> art. 6 ust. 1 lit. c RODO w związku z art. 7 ust. 1 RODO (obowiązek wykazania uzyskania zgody).</li>
        <li><b>Okres przechowywania:</b> zapis wyboru w przeglądarce (plik <code>cookieyes-consent</code>) – 1 rok; rejestr zgód w CookieYes – nie dłużej niż do upływu terminu przedawnienia roszczeń.</li>
      </ul>
    </div>

    <div class="pol-box">
      <h3>h) Statystyki odwiedzin i pomiar kliknięć w numer telefonu (Google Analytics 4)</h3>
      <ul>
        <li><b>Podstawa prawna:</b> zgoda – art. 6 ust. 1 lit. a RODO w związku z art. 399 ustawy Prawo komunikacji elektronicznej.</li>
        <li><b>Okres przechowywania:</b> pliki cookies – do czasu wycofania zgody, nie dłużej niż okres ich ważności opisany w pkt 8; dane statystyczne zebrane w Google Analytics – 14 miesięcy od ich zebrania, po czym są automatycznie usuwane. Wycofanie zgody wstrzymuje dalsze zbieranie danych.</li>
      </ul>
    </div>

    <div class="pol-box">
      <h3>i) Pomiar odwiedzin bez plików cookies przed wyrażeniem zgody (tryb uzyskiwania zgody Google)</h3>
      <ul>
        <li><b>Zakres danych:</b> adres IP (przekazywany przy połączeniu z serwerem Google), adres odwiedzanej podstrony, informacje o przeglądarce i urządzeniu, informacja o kliknięciu w numer telefonu – bez zapisywania plików cookies i bez identyfikatora użytkownika.</li>
        <li><b>Podstawa prawna:</b> art. 6 ust. 1 lit. f RODO (uzasadniony interes administratora polegający na ogólnym pomiarze ruchu na stronie).</li>
        <li><b>Okres przechowywania:</b> 14 miesięcy w Google Analytics, wyłącznie w postaci zbiorczych statystyk.</li>
      </ul>
      <p>Możesz wnieść sprzeciw wobec tego przetwarzania (pkt 6), np. korzystając z dodatku do przeglądarki blokującego Google Analytics.</p>
    </div>

    <div class="pol-box">
      <h3>j) Pomiar skuteczności reklam Google Ads (gdy prowadzone są kampanie)</h3>
      <ul>
        <li><b>Podstawa prawna:</b> zgoda – art. 6 ust. 1 lit. a RODO w związku z art. 399 ustawy Prawo komunikacji elektronicznej.</li>
        <li><b>Okres przechowywania:</b> pliki cookies – do czasu wycofania zgody, nie dłużej niż okres ich ważności opisany w pkt 8; dane o konwersjach w koncie Google Ads – przez okres przechowywania stosowany przez Google, po czym są usuwane.</li>
      </ul>
    </div>

    <p>Podanie danych jest dobrowolne, ale niezbędne do udzielenia odpowiedzi na zapytanie lub wykonania usługi.</p>

    <h2>4. Odbiorcy danych</h2>
    <p>Dane mogą być przekazywane następującym kategoriom odbiorców:</p>
    <ul>
      <li>dostawca hostingu – SEOhost.pl (serwery w Polsce),</li>
      <li>biuro rachunkowe,</li>
      <li>Google Ireland Limited – Google Tag Manager, Google Analytics i Google Ads,</li>
      <li>CookieYes Limited – narzędzie do zarządzania zgodami na pliki cookies,</li>
      <li>OpenStreetMap Foundation – dostarcza fragmenty mapy wyświetlanej na stronie i otrzymuje przy tym adres IP oraz informacje o przeglądarce,</li>
      <li>podmioty uczestniczące w realizacji zlecenia, np. ubezpieczyciele, wypożyczalnie i warsztaty, parkingi, współpracujące firmy pomocy drogowej,</li>
      <li>organy publiczne, jeżeli wymagają tego przepisy prawa.</li>
    </ul>

    <h2>5. Przekazywanie danych poza Europejski Obszar Gospodarczy</h2>
    <ul>
      <li><b>Google LLC (USA)</b> – na podstawie decyzji Komisji Europejskiej z 10 lipca 2023 r. stwierdzającej odpowiedni stopień ochrony (EU-US Data Privacy Framework) oraz standardowych klauzul umownych.</li>
      <li><b>Wielka Brytania</b> (CookieYes Limited, OpenStreetMap Foundation) – na podstawie decyzji Komisji Europejskiej stwierdzającej odpowiedni stopień ochrony.</li>
    </ul>

    <h2>6. Prawa osób, których dane dotyczą</h2>
    <p>Przysługuje Ci prawo do:</p>
    <ul>
      <li>dostępu do danych (art. 15 RODO),</li>
      <li>sprostowania danych (art. 16 RODO),</li>
      <li>usunięcia danych (art. 17 RODO),</li>
      <li>ograniczenia przetwarzania (art. 18 RODO),</li>
      <li>przenoszenia danych (art. 20 RODO),</li>
      <li>sprzeciwu wobec przetwarzania opartego na uzasadnionym interesie administratora (art. 21 RODO),</li>
      <li>cofnięcia zgody w dowolnym momencie – bez wpływu na zgodność z prawem przetwarzania dokonanego przed jej cofnięciem.</li>
    </ul>
    <p>W sprawie realizacji swoich praw skontaktuj się z administratorem, korzystając z danych podanych w pkt 1.</p>
    <p>Masz również prawo wniesienia skargi do Prezesa Urzędu Ochrony Danych Osobowych, ul. Stawki 2, 00-193 Warszawa, <a href="https://uodo.gov.pl/" target="_blank" rel="noopener">https://uodo.gov.pl/</a>.</p>

    <h2>7. Zautomatyzowane podejmowanie decyzji</h2>
    <p>Dane nie są wykorzystywane do zautomatyzowanego podejmowania decyzji, w tym profilowania, wywołującego skutki prawne (art. 22 RODO).</p>

    <h2>8. Pliki cookies</h2>
    <p>Pliki cookies to niewielkie pliki tekstowe zapisywane w przeglądarce użytkownika. Pliki niezbędne do działania strony i zapamiętania Twojego wyboru dotyczącego cookies zapisujemy bez zgody. Pozostałe pliki cookies zapisujemy wyłącznie za Twoją zgodą, którą wyrażasz lub odmawiasz w banerze CookieYes.</p>
    <p>Na stronie mogą być używane następujące kategorie plików cookies:</p>
    <ul>
      <li><b>niezbędne</b> – <code>cookieyes-consent</code> (zapamiętanie wyboru dotyczącego cookies, 1 rok),</li>
      <li><b>analityczne</b> – <code>_ga</code>, <code>_ga_*</code> (Google Analytics, do 13 miesięcy),</li>
      <li><b>reklamowe</b> – np. <code>_gcl_au</code> (Google Ads, 90 dni); używane wyłącznie przy prowadzonych kampaniach reklamowych.</li>
    </ul>
    <p>Do czasu wyrażenia zgody narzędzia Google nie zapisują analitycznych ani reklamowych plików cookies. Mogą jednak przesyłać sygnały o odwiedzinach strony bez plików cookies i bez identyfikatora użytkownika (tryb uzyskiwania zgody Google, Consent Mode) – opis w pkt 3 lit. i.</p>
    <p>Zgodę możesz zmienić lub wycofać w każdej chwili, korzystając z przycisku <button type="button" class="cky-banner-element btn-link">Ustawienia cookies</button> (także w stopce strony) lub w ustawieniach swojej przeglądarki. Zablokowanie plików cookies niezbędnych może utrudnić korzystanie ze strony.</p>

    <h2>9. Mapa</h2>
    <p>Mapy na stronie korzystają z OpenStreetMap. Przy wyświetleniu mapy przeglądarka pobiera fragmenty mapy z serwerów OpenStreetMap Foundation, które otrzymują adres IP użytkownika. Mapa nie zapisuje plików cookies. Zasady przetwarzania danych przez OpenStreetMap Foundation opisuje jej <a href="https://osmfoundation.org/wiki/Privacy_Policy" target="_blank" rel="noopener">polityka prywatności</a>.</p>

    <h2>10. Linki zewnętrzne</h2>
    <p>Strona zawiera odnośniki do Facebooka i Map Google. Po przejściu na te serwisy obowiązują ich własne zasady prywatności.</p>

    <h2>11. Zmiany polityki prywatności</h2>
    <p>Administrator może aktualizować niniejszą politykę. Aktualna wersja jest zawsze dostępna na tej stronie.</p>
  </section>
<?php
include __DIR__ . '/partials/cta.php';
include __DIR__ . '/partials/footer.php';
