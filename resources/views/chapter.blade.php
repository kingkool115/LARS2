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
            <img id="uni_logo" src="/storage/logo-uni-ulm.svg">
        </h2>
    </span>
    <div class="topnav">
        @if (!Auth::guest())
            <a class="left-header-buttons" href="{{ route('lectures') }}">My Lectures</a>
            <a href="#news">Create new survey</a>
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

<main>
    @if (Auth::guest())
        @yield('content')
    @else
        <div id="topic">{{$chapter['name']}}</div>

        <table class="questions_table">
            <tr id="very_first_table_row">
                <th>Survey Name</th>
                <th>
                    <span id="remove-label">Remove</span>
                    <button id="remove-button" type="submit" class="btn btn-primary" style="background-color: #a94442; display: none;" onclick="removeSurveys();"> Remove </button>
                </th>
            </tr>
            @for ($x = 0; $x < 1; $x++)
                <tr class="survey_table_content">
                    <th></th>
                    <th></th>
                </tr>
            @endfor
            @foreach($result as $survey)
                <tr class="survey_table_content">
                    <th>
                        <a href="{{route('survey', ['survey_id' => $survey['id']])}}">
                            {{ $survey['name']}}
                        </a>
                    </th>
                    <th class="remove-checkboxes">
                        <input id="slide_to_remove_{{$survey['id']}}" autocomplete="off" type="checkbox" name="question-to-remove" onchange="displayHideRemoveButton();">
                    </th>
                </tr>
            @endforeach
        </table>

        <table id="textfield-new-slide">
            <tr>
                <th><input type="text" name="new_slide_number"></th>
                <th>
                    <button type="submit" class="btn btn-primary" onclick="createNewSlide()">Create</button>
                </th>
            </tr>
        </table>
        <div id="error-message"></div>
    @endif
</main>

</body>
</html>