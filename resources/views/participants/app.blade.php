<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:100,300,400">
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
    <link rel="stylesheet" href="{{asset('css/participant.css')}}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    <title>@yield('title')</title>
</head>
<body>
<header>
    @if($info['eventId'] === '12')
        <img class="header-logo" alt="logo"
             src="{{ asset('images/live-stage.png') }}"/>
    @else
        <img class="header-logo" alt="logo"
             src="https://patriotsport.moscow/wp-content/uploads/2020/04/artboard-3.svg?1626259592"/>
    @endif

</header>
<div class="container">
    <div class="qr-code">
        @yield('content')
    </div>
</div>


<div class="block-gradient-top"></div>
<div class="block-gradient-bottom"></div>
</body>
</html>
