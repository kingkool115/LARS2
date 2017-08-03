<!DOCTYPE html>
<html lang="de" class="no-js" xmlns:javascript="http://www.w3.org/1999/xhtml">
<head>

    <meta charset="utf-8">

    <meta name="language" content="de">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/css.css') }}" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <title>LARS - Home </title>
    <meta name="title" content="LARS - Universität Ulm" />
    <meta name="date" content="2017-04-24" />
</head>

<body>
<header>

    <span>
        <h2>
            LARS - Laravel Audience Response System
            <img id="uni_logo" src="/storage/app/logo-uni-ulm.svg">
        </h2>
    </span>
    <div class="topnav">
        @if (!Auth::guest())
            <a class="left-header-buttons" href="{{ route('overview') }}">Overview</a>
            <a href="{{ route('lectures') }}">My Lectures</a>
            <a href="{{route('show_create_survey_form')}}">Create new survey</a>
            <!-- Handle Logout Button -->
            <a id="logout_button" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
            <div id="logged_in_as">Angemeldet als: {{ Auth::user()->name }}</div>
        @endif
    </div>
</header>

@if (!Auth::guest())
    @yield('dashboard_content')
@endif
</body>