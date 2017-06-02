
<!DOCTYPE html>
<html lang="de" class="no-js">
<head>

    <meta charset="utf-8">

    <meta name="language" content="de">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link href="{{ asset('css/css.css') }}" rel="stylesheet">

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

    <div id="topic">My Lectures</div>

    <div class="css-treeview">
        <ul>
            <!-- 1st level -->
            @for ($x = 0; $x < count($lectures); $x++)
            <li><input type="checkbox" id="item-{{$x}}" /><label for="item-{{$x}}">{{ $lectures[$x]->getName() }}</label>
                <ul>
                    @for ($y = 0; $y < count($lectures[$x]->getChapters()); $y++)
                            <!-- 2nd level -->
                    <li><input type="checkbox" id="item-{{$x}}-{{$y}}" /><label for="item-{{$x}}-{{$y}}">{{ $lectures[$x]->getChapters()[$y]->getName() }}</label>
                        <ul>
                            @foreach ($lectures[$x]->getChapters()[$y]->getSurveys() as $survey)
                                    <!-- 3rd Level -->
                            <li><a href={{route('survey', ['survey_id' => $survey->getId()])}}>{{ $survey->getName() }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                    @endfor
                </ul>
            </li>
            @endfor
        </ul>
    </div>
    @endif
</main>


</body>
</html>