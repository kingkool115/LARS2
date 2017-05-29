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
            <a class="left-header-buttons" href="#home">My Lectures</a>
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
        <div id="topic">Welcome to your survey page!!</div>
    @endif

    <table class="questions_table">
        <tr id="very_first_table_row">
            <th id="questoin_column">Question</th>
            <th>Slide Number</th>
        </tr>
    @for ($x = 0; $x < 1; $x++)
        <tr class="survey_table_content">
            <th></th>
            <th></th>
        </tr>
    @endfor
    @foreach($result as $question)
        <tr class="survey_table_content">
            <th>
                <a href="{{route('question', ['survey_id' => $question['survey_id'], 'slide_number' => $question['slide_number']])}}">
                    {{ $question['question']}}
                </a>
            </th>
            <th class="slide_numbers">{{ $question['slide_number'] }}</th>
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
        <script>
            function createNewSlide() {
                var new_slide_number = document.getElementsByName("new_slide_number")[0].value;
                $.getJSON( "{{Request::url()}}" + "/slide_number_exists/" + new_slide_number, function( data ) {
                    if (!Number.isInteger(parseInt(new_slide_number))) {
                    document.getElementById("error-message").innerHTML = "'" + new_slide_number + "' is not a numbver.";
                    document.getElementById("error-message").display = display;
                    }
                    else if (data['slideNumberExists']) {
                        document.getElementById("error-message").innerHTML = "slide number " + new_slide_number + " already exists for this survey.";
                        document.getElementById("error-message").display = display;
                        //window.alert("slide number " + new_slide_number + " already exists for this survey.");
                    } else {
                        document.getElementById("error-message").innerHTML = "";
                        window.location.href = "/survey/{{$question['survey_id']}}/slide_number/" + new_slide_number;
                    }
                });
            }
        </script>
</main>

</body>
</html>
