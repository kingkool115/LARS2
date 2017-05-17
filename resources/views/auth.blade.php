
<!DOCTYPE html>
<html lang="de" class="no-js">
<head>

    <meta charset="utf-8">

    <meta name="language" content="de">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LARS') }}</title>


    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/css.css') }}" rel="stylesheet">

    <title>LARS - Home </title>
    <meta name="title" content="LARS - Universität Ulm" />
    <meta name="date" content="2017-04-24" />
</head>

<body>

<header>

    <span>
        <h2 style="margin:1%;">
            LARS - Laravel Audience Response System
            <img id="uni_logo" src="/storage/logo-uni-ulm.svg">
        </h2>
    </span>
    <div class="topnav">
    </div>
</header>

<main style="margin-left: inherit; margin-right: inherit;">
    @if (Auth::guest())
        @yield('content')
    @endif
</main>


</body>
</html>