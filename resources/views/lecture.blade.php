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
        <div id="topic">{{$lecture['name']}}</div>

        <table class="chapters_table">
            <tr id="very_first_table_row">
                <th>Chapter Name</th>
                <th>
                    <div id="remove-label">Remove</div>
                    <button id="remove-button" type="submit" class="btn btn-primary" style="background-color: #a94442; display: none;" onclick="removeChapters();"> Remove </button>
                </th>
            </tr>
            @for ($x = 0; $x < 1; $x++)
                <tr class="survey_table_content">
                    <th></th>
                    <th></th>
                </tr>
            @endfor
            @foreach($result as $chapter)
                <tr class="survey_table_content">
                    <th>
                        <a href="{{route('chapter', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter['id']])}}">
                            {{ $chapter['name']}}
                        </a>
                    </th>
                    <th class="remove-checkboxes">
                        <input id="chapter_to_remove_{{$chapter['id']}}" autocomplete="off" type="checkbox" name="chapter-to-remove" onchange="displayHideRemoveButton();">
                    </th>
                </tr>
            @endforeach
        </table>

        <table id="textfield-new-chapter">
            <tr>
                <th><input type="text" name="new_chapter" placeholder="Enter chapter name"></th>
                <th>
                    <button type="submit" class="btn btn-primary" onclick="createNewChapter()">Create chapter</button>
                </th>
            </tr>
        </table>
        <div id="error-message"></div>
    @endif
</main>

<script>

    /**
     * This function creates a new chapter if Create-Button is clicked.
     */
    function createNewChapter() {
        var chapter_name = document.getElementsByName("new_chapter")[0].value;
        var url = "{{route('create_chapter', ['lecture_id' => $lecture_id, 'chapter_name' => 'new_chapter_name'])}}";
        url = url.replace('new_chapter_name', chapter_name);
        window.location.href = url;
    }

    /**
     * This function will display Remove Button when at least one checkbox is checked.
     * Otherwise there is just a label visible.
     */
    function displayHideRemoveButton() {
        var all_checkboxes = document.getElementsByName('chapter-to-remove');
        for (x = 0; x < all_checkboxes.length; x++) {
            if (all_checkboxes[x].checked) {
                document.getElementById('remove-label').style.display = 'none';
                document.getElementById('remove-button').style.display = 'block';
                document.getElementById('remove-button').style.margin = '0 auto';
                return;
            }
        }
        document.getElementById('remove-label').style.display = 'block';
        document.getElementById('remove-button').style.display = 'none';
    }

    /**
     * Is called, when remove button is clicked.
     * It detects which chapters are marked to be removed and concat them to a string.
     * This string of concatenated chapter ids will be passed as parameter to the URL which handles the deletion.
     */
    function removeChapters() {
        var all_checkboxes = document.getElementsByName('chapter-to-remove');
        var chapters_to_remove = "";
        for (x = 0; x < all_checkboxes.length; x++) {
            if (all_checkboxes[x].checked) {
                var checkbox_id = all_checkboxes[x].id;
                var chapter_id = checkbox_id.split("chapter_to_remove_")[1];
                chapters_to_remove += chapter_id + '_';
            }
        }
        // remove last underline character
        chapters_to_remove = chapters_to_remove.slice(0, -1);
        var url = '{{route('remove_chapters', ['lecture_id' => $lecture_id])}}';
        // put slides_to_remove as parameter into the url
        window.location.href = url + "?chapters_to_remove=" + chapters_to_remove;
    }
</script>
</body>
</html>