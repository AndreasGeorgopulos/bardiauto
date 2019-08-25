<h1>Bárdi Autó teszt feladat</h1>

<p>Copyright &copy; 2019, Georgopulosz Andreasz</p>
<p>&nbsp;</p>

<p>A tesztfeladat Laravel 5.8-as keretrendszerben VueJS és Bootstrap 4.3.1 felhasználásával készült.</p>

<p>
A feladatok megtekintéséhez szükséges eszközök:
<ul>
    <li>Composer (<a href="https://getcomposer.org">Letöltés</a>)</li>
    <li>Git (<a href="https://git-scm.com/downloads">Letöltés</a>)</li>
    <li>Minimum PHP 7.1.3</li>
    <li>MySql</li>
</ul>
</p>

<p>
Telepítés:
<ul>
    <li>project mappa ([mappa]) létrehozása, majd belépés a mappába</li>
    <li>Git clone: git clone https://github.com/AndreasGeorgopulos/bardiauto .</li>
    <li>Laravel telepítés: composer install</li>
    <li>Adatbázis beállítások: .env file DB_DATABASE, DB_USERNAME, DB_PASSWORD</li>
    <li>Adatbázis migráció: php artisan migrate:refresh --seed</li>
    <li>Indítás: php artisan serve (Böngésző: http://localhost:8000)</li>
</ul>
</p>


Források:
<ul>
    <li>Route config: routes/web.php</li>
    <li>Adatbázis migrációk: database/migrations/*</li>
    <li>Adatbázis seed: database/seeds/SeatsTableSeeder.php</li>
    <li>Controller: App/Http/Controllers/ReservationController.php</li>
    <li>Model-ek: App/Order.php, App/Reservation.php, App/Seat.php, App/Transaction.php</li>
    <li>Frontend (html, css, js): resources/views/index.blade.php</li>
    <li>E-mail template: resources/views/order_email.blade.php</li>
</ul>