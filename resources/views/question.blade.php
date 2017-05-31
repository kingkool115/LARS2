<!DOCTYPE html>
<html lang="de" class="no-js">
<head>

    <meta charset="utf-8">

    <meta name="language" content="de">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
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
            Survey '{{$survey_name}}'<br/>
            Edit question for slide number {{$slide_number}}
        </div>

    <!-- Radion Buttons to select type of question -->
    <form onclick="displayCorrectForm()">
        <input type="radio" id="multiple-choice-radio" name="question-type" value="multiple-choice" checked>Multiple choice question<br>
        <input type="radio" id="text-response-radio" name="question-type" value="text-response">Text respone question
    </form>
    <br>
    <br>
    <br>
    <br>

    <!-- Form to create a text response question -->
    <form id="text-response-form" action="/create_question?survey_id={{$survey_id}}&slide_number={{$slide_number}}" method="post" enctype="multipart/form-data">
        <div class="form-group question-form">
            <!-- Question -->
            <label for="question">Question</label>
            <textarea rows="4" cols="50" class="form-control" name="question" required autofocus></textarea>

            <!-- Image Upload -->
            {{ csrf_field() }}
            <label id="image-upload" for="question-image">Select image</label><br>
            <input type="file" name="question-image" id="fileToUpload"><br><br>

            <!-- Correct answer -->
            <label id="correct-answer" for="correct_answer">Correct answer</label>
            <input type="text" class="form-control" name="correct-answer" required autofocus><br><br>

            <!-- Choose where to show the results of the students -->
            <label for="when-to-show-results"> When do you want to display the results of the question?</label><br>
            <input type="radio" name="when-to-show-results" value="next-slide" required> Show results on next slide<br>
            <input type="radio" name="when-to-show-results" value="end-of-chapter"> Show results at the end of chapter<br><br>

            <!-- Submit Button -->
            <button id="submit-question-button"class="btn btn-primary" type="submit"> Create Question</button>
        </div>
    </form>

    <!-- Form to create a multiple choice question -->
    <form id="multiple-choice-form" style="display: none" action="/create_question?survey_id={{$survey_id}}&slide_number={{$slide_number}}" method="post" enctype="multipart/form-data">
        <div class="form-group question-form">
            <!-- Question -->
            <label for="question">Question</label>
            <textarea rows="4" cols="50" class="form-control" name="question" required autofocus></textarea>

            <!-- Image Upload -->
            {{ csrf_field() }}
            <label id="image-upload" for="question-image">Select image</label><br/>
            <input type="file" name="question-image" id="fileToUpload"><br><br>

            <!-- Max. 8 possible answers -->
            <label for="correct-answer"> Create your possible answers and mark the correct ones</label><br><br>
            @for ($x = 1; $x < 8; $x++)
                <label id="correct_answer_{{$x}}" for="correct_answer">Answer {{$x}}</label>
                <div style="float: right">
                    <input type="checkbox" name="correct-answers" id="correct_answer_checkbox_{{$x}}"> correct
                </div>
                <textarea rows="1" cols="50" class="form-control" name="possible_answer" id="possible_answer_{{$x}}"></textarea>
                <br>
            @endfor
            <br>
            <br>

            <!-- Choose where to show the results of the students -->
            <label for="when-to-show-results"> When do you want to display the results of the question?</label><br>
            <input type="radio" name="when-to-show-results" value="next-slide" required> Show results on next slide<br>
            <input type="radio" name="when-to-show-results" value="end-of-chapter"> Show results at the end of chapter<br><br>

            <!-- Error Message if answers not filled out correctly-->
            <div id="error-message"></div>

            <!-- Submit Button -->
            <button id="submit-question-button"class="btn btn-primary" type="submit" onclick="return validateQuestions()">
                Create Question
            </button>
        </div>
    </form>


    <script>
        function validateQuestions() {
            var correct_answers = 0;
            var checked_empty_answer_as_correct = false;
            for (var x = 1; x < document.getElementsByName('correct-answers').length; x++) {
                var possible_answer = document.getElementById('possible_answer_' + x).value;
                var is_checkbox_checked = document.getElementById('correct_answer_checkbox_' + x).checked;
                if (possible_answer.trim().length == 0 && is_checkbox_checked) {
                    checked_empty_answer_as_correct = true;
                }
                if (possible_answer.trim().length > 0 && is_checkbox_checked) {
                    correct_answers += 1;
                }
            }

            if (correct_answers == 0) {
                document.getElementById("error-message").innerHTML = "You have to set at least one correct answer."
                return false;
            }
            if (checked_empty_answer_as_correct) {
                document.getElementById("error-message").innerHTML = "You can not mark an empty answer as correct."
                return false;
            }
            if (correct_answers > 0) {
                // validation ok
                document.getElementById("error-message").innerHTML = "";
                return true;
            }
        }

        function displayCorrectForm() {
            var multiple_choice_radio_button = document.getElementById('multiple-choice-radio');

            var text_response_form = document.getElementById('text-response-form');
            var multiple_choice_form = document.getElementById('multiple-choice-form');

            //alert('multiple choice checked: ' + is_multiple_choice_radio_button_checked)
            if (multiple_choice_radio_button.checked) {
                multiple_choice_form.style.display = "initial";
                text_response_form.style.display = "none";
            } else {
                multiple_choice_form.style.display = "none";
                text_response_form.style.display = "initial";
            }
            document.getElementById('multiple-choice-radio')
        };
        displayCorrectForm();
    </script>

    @endif
</main>

</body>
</html>
