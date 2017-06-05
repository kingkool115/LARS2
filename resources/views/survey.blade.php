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
        <div id="topic">
            <a href="{{route('chapter', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id])}}">Chapter {{$chapter_name['name']}}</a><br>
            {{$survey['name']}}
        </div>

        <table class="questions_table">
            <tr id="very_first_table_row">
                <th id="question_column">Question</th>
                <th>Slide Number</th>
                <th>
                    <div id="remove-label">Remove</div>
                    <button id="remove-button" type="submit" class="btn btn-primary" style="background-color: #a94442; display: none;" onclick="removeQuestions();"> Remove </button>
                </th>
            </tr>
        @for ($x = 0; $x < 1; $x++)
            <tr class="survey_table_content">
                <th></th>
                <th></th>
                <th></th>
            </tr>
        @endfor
        @foreach($result as $question)
            <tr class="survey_table_content">
                <th>
                    <a href="{{route('question', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $question['survey_id'], 'slide_number' => $question['slide_number']])}}">
                        {{ $question['question']}}
                    </a>
                </th>
                <th class="slide_numbers">{{ $question['slide_number'] }}</th>
                <th class="remove-checkboxes">
                    <input id="slide_to_remove_{{$question['slide_number']}}" autocomplete="off" type="checkbox" name="question-to-remove" onchange="displayHideRemoveButton();">
                </th>
            </tr>
        @endforeach
        </table>

            <table id="textfield-new-slide">
                <tr>
                    <th><input type="text" name="new_slide_number" placeholder="slide number"></th>
                    <th>
                        <button type="submit" class="btn btn-primary" onclick="createNewSlide()">Create question</button>
                    </th>
                </tr>
            </table>
                <div id="error-message"></div>
            <script>

                /**
                 * This function is called when Create-Button is clicked and will redirect you to editQuestion-view.
                 * It throws an error message when input is not an positive integer.
                 * It throws an error message when you want to create a slide number that already exists.
                 */
                function createNewSlide() {
                    var new_slide_number = document.getElementsByName("new_slide_number")[0].value;
                    var url_check_if_slide_number_exists = "{{route('slide_number_exists', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id, 'slide_number' => 'new_slide_number'])}}";
                    url_check_if_slide_number_exists = url_check_if_slide_number_exists.replace('new_slide_number', new_slide_number);
                    $.getJSON( url_check_if_slide_number_exists, function( data ) {
                        if (!Number.isInteger(parseInt(new_slide_number))) {
                            document.getElementById("error-message").innerHTML = "'" + new_slide_number + "' is not a number.";
                            document.getElementById("error-message").display = display;
                        }
                        else if (data['slideNumberExists']) {
                            document.getElementById("error-message").innerHTML = "slide number " + new_slide_number + " already exists for this survey.";
                            document.getElementById("error-message").display = display;
                            //window.alert("slide number " + new_slide_number + " already exists for this survey.");
                        } else {
                            document.getElementById("error-message").innerHTML = "";
                            var url = "{{route('question', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id, 'slide_number' => 'new_slide_number'])}}";
                            url = url.replace('new_slide_number', new_slide_number);
                            window.location.href = url;
                        }
                    });
                }

                /**
                 * This function will display Remove Button when at least one checkbox is checked.
                 * Otherwise there is just a label visible.
                 */
                function displayHideRemoveButton() {
                    var all_checkboxes = document.getElementsByName('question-to-remove');
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
                 * It detects which slide numbers are marked to be removed and concat them to a string.
                 * This string of concatenated slide numbers will be passed as parameter to the URL which handles the deletion.
                 */
                function removeQuestions() {
                    var all_checkboxes = document.getElementsByName('question-to-remove');
                    var slides_to_remove = "";
                    for (x = 0; x < all_checkboxes.length; x++) {
                        if (all_checkboxes[x].checked) {
                            var checkbox_id = all_checkboxes[x].id;
                            var slide_number = checkbox_id.split("slide_to_remove_")[1];
                            slides_to_remove += slide_number + '_';
                        }
                    }
                    // remove last underline character
                    slides_to_remove = slides_to_remove.slice(0, -1);
                    var url = '{{route('remove_questions', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id])}}';
                    // put slides_to_remove as parameter into the url
                    window.location.href = url + "?slides_to_remove=" + slides_to_remove;
                }
            </script>
        @endif
</main>

</body>
</html>
