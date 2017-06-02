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
            <a href="{{route('survey', ['survey_id' => $survey_id])}}"> Survey '{{$survey_name}}' </a><br/>
            @if ($edit_form)
                Edit
            @else
                Create
            @endif question for slide number {{$slide_number}}
        </div>

    <!-- Radion Buttons to select type of question -->
    <form onclick="displayCorrectForm()">
        <input type="radio" id="multiple-choice-radio" name="question-type" value="multiple-choice"
               @if($edit_form && !$question['is_text_response']) checked @endif required>
                            Multiple choice question<br>
        <input type="radio" id="text-response-radio" name="question-type" value="text-response"
               @if($edit_form && $question['is_text_response']) checked @endif required>
                                Text respone question
    </form>
    <br>
    <br>
    <br>
    <br>

    <!-- Form to create a text response question -->
    <form id="text-response-form" action="/create_text_response_question?survey_id={{$survey_id}}&slide_number={{$slide_number}}"
          method="post" enctype="multipart/form-data" autocomplete="off"> <!-- when page is reloaded, than reload form from DB.  -->
        <div class="form-group question-form">
            <!-- Question -->
            <label for="question">Question</label>
            <!-- has to be this long line due to text intend issues when breaking lines between textarea tags-->
            <textarea rows="4" cols="50" class="form-control" name="question"
                      required>@if($edit_form && isset($question['correct_text_response'])){{$question['question']}}@endif</textarea>

            <!-- Image Upload -->
            {{ csrf_field() }}
            <label id="image-upload" for="question-image-text-response">Select image</label><br/>
            <div class="file-upload-area">
                <span id="actual-image-text-response"> Actual Image: </span>
                @if($edit_form && $question['image_path'])
                    <img id="display-image-text-response" width="300" height="200" onerror="this.style.visibility='hidden'" src="<?php
                    if ($question['is_text_response']) {
                        echo asset("storage/" . $question['image_path']);
                    }?>" ><br><br>
                @else
                    <img id="display-image-text-response" onerror="this.style.visibility='hidden'"  width="300" height="200" src=""><br><br>
                @endif
                <input type="file" style="color: transparent;" name="question-image-text-response" id="fileToUpload" onchange="newImageSelected(event, false)">
            </div>
            <br>
            <br>

            <!-- Correct answer -->
            <label id="correct-answer" for="correct_answer">Correct answer</label>
            @if($edit_form && isset($question['correct_text_response']))
                <input type="text" class="form-control" name="correct-answer" value="{{$question['correct_text_response']}}" required><br><br>
            @else
                <input type="text" class="form-control" name="correct-answer" required><br><br>
            @endif

            <!-- Choose where to show the results of the students -->
            <label for="when-to-show-results"> When do you want to display the results of the question?</label><br>
            <input type="radio" name="when-to-show-results" value="next-slide"
                   @if($edit_form && $question['show_result_on_next_slide']) checked @endif required> Show results on next slide<br>
            <input type="radio" name="when-to-show-results" value="end-of-chapter"
                   @if($edit_form && !$question['show_result_on_next_slide']) checked @endif> Show results at the end of chapter<br><br>

            <!-- Submit Button -->
            <button id="submit-question-button"class="btn btn-primary" type="submit">
                @if($edit_form)
                    Save
                @else
                    Create
                @endif
                    Question
            </button>
        </div>
    </form>

    <!-- Form to create a multiple choice question -->
    <form id="multiple-choice-form" style="display: none" action="/create_multiple_choice_question?survey_id={{$survey_id}}&slide_number={{$slide_number}}"
          method="post" enctype="multipart/form-data" autocomplete="off"> <!-- when page is reloaded, than reload form from DB.  -->
        <div class="form-group question-form">
            <!-- Question -->
            <textarea rows="4" cols="50" class="form-control" name="question"
                      required>@if($edit_form && sizeof($question['correct_text_response']) == 0){{$question['question']}}@endif</textarea>

            <!-- Image Upload -->
            {{ csrf_field() }}
            <label id="image-upload" for="question-image-multiple-choice">Select image</label><br/>
                <div class="file-upload-area">
                    <span id="actual-image-multiple-choice"> Actual Image: </span>
                    @if($edit_form && $question['image_path'])
                        <img id="display-image-multiple-choice" width="300" height="200" onerror="this.style.visibility='hidden'" src="<?php
                        if (!$question['is_text_response']) {
                            echo asset("storage/" . $question['image_path']);
                        }?>" ><br><br>
                    @else
                        <img id="display-image-multiple-choice" width="300" height="200" onerror="this.style.visibility='hidden'" src=""><br><br>
                    @endif
                        <input type="file" style="color: transparent;" name="question-image-multiple-choice" id="fileToUpload" onchange="newImageSelected(event, true)">
                </div>
            <br><br>

            <!-- Max. 8 possible answers -->
            <label for="correct-answer"> Create your possible answers and mark the correct ones</label><br><br>
            @for ($x = 1; $x < 8; $x++)
                <label id="correct_answer_{{$x}}" for="correct_answer">Answer {{$x}}</label>
                <div style="float: right">
                    @if ($edit_form && !$question['is_text_response'] && sizeof(explode('-', $question['correct_answers'])) > $x && explode('-', $question['correct_answers'])[$x - 1])
                        <input type="checkbox" name="is_answer_correct_{{$x}}" id="correct_answer_checkbox_{{$x}}" checked> correct
                    @else
                        <input type="checkbox" name="is_answer_correct_{{$x}}" id="correct_answer_checkbox_{{$x}}" > correct
                    @endif
                </div>
                <textarea rows="1" cols="50" class="form-control" name="possible_answer_{{$x}}" id="possible_answer_{{$x}}"
                    >@if ($edit_form && !isset($question['correct_text_response'])){{$question['answer_' . $x]}}@endif</textarea>
                <br>
            @endfor
            <br>
            <br>

            <!-- Choose where to show the results of the students -->
            <label for="when-to-show-results"> When do you want to display the results of the question?</label><br>
            <input type="radio" name="when-to-show-results" value="next-slide"
                   @if($edit_form && $question['show_result_on_next_slide']) checked @endif required> Show results on next slide<br>
            <input type="radio" name="when-to-show-results" value="end-of-chapter"
                   @if($edit_form && !$question['show_result_on_next_slide']) checked @endif> Show results at the end of chapter<br><br>

            <!-- Error Message if answers not filled out correctly-->
            <div id="error-message"></div>

            <!-- Submit Button -->
            <button id="submit-question-button"class="btn btn-primary" type="submit" onclick="return validateQuestions()">
                @if($edit_form)
                    Save
                @else
                    Create
                @endif
                Question
            </button>
        </div>
    </form>


    <!-- My image popup -->
    <div id="myModal" class="modal">
        <span class="close">&times;</span>
        <img class="modal-content" id="img01">
        <div id="caption"></div>
    </div>
    <script>
        // Get the modal
        var modal = document.getElementById('myModal');

        // Get the image and insert it inside the modal - use its "alt" text as a caption
        @if(isset($question['is_text_response']) && $question['is_text_response'])
            var img = document.getElementById('display-image-text-response');
        @else
            var img = document.getElementById('display-image-multiple-choice');
        @endif

        var modalImg = document.getElementById("img01");
        var captionText = document.getElementById("caption");
        img.onclick = function(){
            modal.style.display = "block";
            modalImg.src = this.src;
            captionText.innerHTML = this.alt;
        }

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }
    </script>

    <script>
        function validateQuestions() {
            <!-- This function checks if at least one question in multiple choice mode is set to correct.-->
            <!-- It also checks if an empty text field is set to correct. -->
            var correct_answers = 0;
            var checked_empty_answer_as_correct = false;
            for (var x = 1; x < 8; x++) {
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

        <!-- this function helps to display either text response form OR multiple choice form -->
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

        function newImageSelected(event, is_multiple_choice) {
            if (is_multiple_choice) {
                var output = document.getElementById('display-image-multiple-choice');
                var description = document.getElementById('actual-image-multiple-choice');
                description.innerHTML = "New image: ";
                description.style.color = "green";
                output.src = URL.createObjectURL(event.target.files[0]);
                output.style.visibility = 'visible';
            } else {
                var output = document.getElementById('display-image-text-response');
                var description = document.getElementById('actual-image-text-response');
                description.innerHTML = "New image: ";
                description.style.color = "green";
                output.src = URL.createObjectURL(event.target.files[0]);
                output.style.visibility = 'visible';
            }
        }

    </script>

    @endif
</main>

</body>
</html>
