<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
</head>

<body>
<h1>T. {{$order->lastname}} {{$order->firstname}}!</h1>
<p>Köszönjük rendelését. A következő üléseket foglalta le:</p>
<ul>
    @foreach($order->reservations as $reservation)
        <li>{{$reservation->seat->row}}. {{$reservation->seat->serial}}. szék</li>
    @endforeach
</ul>
</body>
</html>