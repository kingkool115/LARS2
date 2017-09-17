@extends('user_dashboard')

@section('dashboard_content')
<main>
    @if (Auth::guest())
        @yield('content')
    @else
        <div id="topic">
            <a href="{{route('survey', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id] )}}"> Survey '{{$survey->name}}' </a><br/>
            @if (isset($question))
                Edit {{$question->question}}
            @else
                Create new question
            @endif
        </div>

    <!-- Radio Buttons to select type of question -->
    <form onclick="displayCorrectForm()">
        <input type="radio" id="multiple-choice-radio" name="question-type" value="multiple-choice"
               @if(isset($question) && !$question->is_text_response) checked @endif required>
                            Multiple choice question<br>
        <input type="radio" id="text-response-radio" name="question-type" value="text-response"
               @if((isset($question) && $question->is_text_response) || !isset($question)) checked @endif required>
                                Text respone question
    </form>
    <br>
    <br>
    <br>
    <br>

    <!-- Form to create a text response question -->
    <form id="text-response-form" action="{{route('postTextResponseQuestion', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id])}}"
          method="post" enctype="multipart/form-data" autocomplete="off"> <!-- when page is reloaded, than reload form from DB.  -->
        <div class="form-group question-form">

            <!-- In case we want to just edit an existing question pass the question id-->
            @if(isset($question))
                <input type="hidden" name="question_id" value="{{$question->id}}">
            @endif

            <!-- Question -->
            <label for="question">Question</label>
            <!-- has to be this long line due to text intend issues when breaking lines between textarea tags-->
            <textarea rows="4" cols="50" class="form-control" name="question"
                      required>@if(isset($question) && $question->is_text_response){{$question->question}}@endif</textarea>

            <!-- Image Upload -->
            {{ csrf_field() }}
            <label id="image-upload" for="question-image-text-response">Select image</label><br/>
            <div class="file-upload-area">
                <span id="actual-image-text-response"> Actual Image: </span>
                @if(isset($question) && $question->image_path)
                    @if($question->is_text_response)
                        <img id="display-image-text-response" width="300" height="200" onerror="this.style.visibility='hidden'"
                             src="{{route('question_image',  ['lecture_id' => $lecture_id, 'filename' => $question->image_path])}}" ><br><br>
                    @else
                        <img id="display-image-text-response" width="300" height="200" onerror="this.style.visibility='hidden'"
                             src="" ><br><br>
                    @endif
                @else
                    <img id="display-image-text-response" onerror="this.style.visibility='hidden'"  width="300" height="200" src=""><br><br>
                @endif
                <input type="file" style="color: transparent;" name="question-image-text-response" id="fileToUpload" onchange="newImageSelected(event, false)">
            </div>
            <br>
            <br>

            <!-- Correct answer -->
            <label id="correct-answer" for="correct_answer">Correct answer</label>
            @if(isset($question) && isset($answers) && $answers->count() < 2)
                @foreach ($answers as $answer)
                    <input type="text" class="form-control" name="correct-answer" value="{{$answer->answer}}" required><br><br>
                @endforeach
            @else
                <input type="text" class="form-control" name="correct-answer" required><br><br>
            @endif

            <!-- Submit Button -->
            <button id="submit-question-button"class="btn btn-primary" type="submit">
                @if(isset($question))
                    Save
                @else
                    Create
                @endif
                    Question
            </button>
        </div>
    </form>

    <!-- Form to create a multiple choice question -->
    <form id="multiple-choice-form" style="display: none" action="{{route('postMultipleChoiceQuestion', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id])}}"
          method="post" enctype="multipart/form-data" autocomplete="off"> <!-- when page is reloaded, than reload form from DB.  -->
        <div class="form-group question-form">

            <!-- In case we want to just edit an existing question pass the question id-->
            @if(isset($question))
                <input type="hidden" name="question_id" value="{{$question->id}}">
            @endif

            <!-- Question -->
            <textarea rows="4" cols="50" class="form-control" name="question"
                      required>@if(isset($question) && !$question->is_text_response){{$question->question}}@endif</textarea>

            <!-- Image Upload -->
            {{ csrf_field() }}
            <label id="image-upload" for="question-image-multiple-choice">Select image</label><br/>
                <div class="file-upload-area">
                    <span id="actual-image-multiple-choice"> Actual Image: </span>
                    @if(isset($question) && $question->image_path)
                        @if(!$question->is_text_respone)
                        <img id="display-image-multiple-choice" width="300" height="200" onerror="this.style.visibility='hidden'"
                             src="{{route('question_image',  ['lecture_id' => $lecture_id, 'filename' => $question->image_path])}}" ><br><br>
                        @else
                            <img id="display-image-multiple-choice" width="300" height="200" onerror="this.style.visibility='hidden'"
                                 src="" ><br><br>
                        @endif
                    @else
                        <img id="display-image-multiple-choice" width="300" height="200" onerror="this.style.visibility='hidden'" src=""><br><br>
                    @endif
                        <input type="file" style="color: transparent;" name="question-image-multiple-choice" id="fileToUpload" onchange="newImageSelected(event, true)">
                </div>
            <br><br>

            <!-- at least. 2 possible answers -->
            <label for="correct-answer"> Create your possible answers and mark the correct ones</label><br><br>
            <div id="container_possible_answers">
                <!-- if we want to create a new question or the question we want to edit was a text_response_question -->
                @if(!isset($question))
                    <!-- empty possible answer fields -->
                    @for ($x = 1; $x < 3; $x++)
                        <div id="answer_container_{{$x + 1}}">
                            <label id="correct_answer_{{$x}}" for="correct_answer">Answer {{$x}}</label>
                            <div style="float: right">
                                <input type="checkbox" name="is_answer_correct_{{$x}}" id="correct_answer_checkbox_{{$x}}" > correct
                                <input type="image" class="remove_icon" src="<?php echo (url('/remove_icon.png')); ?>" onclick="removeQuestion({{$x + 1}}); return false;" />
                            </div>
                            <textarea rows="1" cols="50" class="form-control" name="possible_answer_{{$x}}" id="possible_answer_{{$x}}"></textarea>
                            <br>
                        </div>
                    @endfor
                @else
                    <!-- possible answer fields with saved content -->
                    @foreach ($answers as $x => $answer)
                        <div id="answer_container_{{$x + 1}}">
                            <label id="correct_answer_{{$x + 1}}" for="correct_answer">Answer {{$x + 1}}</label>
                            <div style="float: right">
                                @if (isset($question) && !$question->is_text_response && $answer->is_correct)
                                    <input type="checkbox" name="is_answer_correct_{{$x + 1}}" id="correct_answer_checkbox_{{$x + 1}}" checked> correct
                                @else
                                    <input type="checkbox" name="is_answer_correct_{{$x + 1}}" id="correct_answer_checkbox_{{$x + 1}}" > correct
                                @endif
                                    <input type="image" class="remove_icon" src="<?php echo (url('/remove_icon.png')); ?>" onclick="removeQuestion({{$x + 1}}); return false;"/>
                            </div>
                            <textarea rows="1" cols="50" class="form-control" name="possible_answer_{{$x + 1}}" id="possible_answer_{{$x + 1}}"
                            >@if (isset($question->id) && !$question->is_text_response){{$answer->answer}}@endif</textarea>
                            <br>
                        </div>
                    @endforeach
                @endif
            </div>
            <!-- Add answer Button -->
            <button id="add-answer-button"class="btn btn-primary" onclick="addAnswer(); return false;"> Add answer </button>
            <br>
            <br>

            <!-- Error Message if answers not filled out correctly-->
            <div id="error-message"></div>

            <!-- Submit Button -->
            <button id="submit-question-button"class="btn btn-primary" type="submit" onclick="return validateQuestions()">
                @if(isset($question))
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
        @if(isset($question->is_text_response))
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
        function addAnswer(){
            // Number of current possible answers
            var actualNumberOfPossibleAnswers = document.getElementById("container_possible_answers").childElementCount;
            // because every question is composed of 4 html tags
            var newAnswerIndex = actualNumberOfPossibleAnswers + 1;

            // Container <div> where dynamic content will be placed
            var container_possible_answers = document.getElementById("container_possible_answers");

            // Append a node with a random text
            // create label
            var label = document.createElement("label");
            label.id = "correct_answer_" + newAnswerIndex;
            label.for = "correct_answer";
            label.innerHTML = "Answer " + newAnswerIndex;

            // create div for checkbox and textarea
            var div = document.createElement("div");
            div.style.float = "right";

            // create checkbox
            var checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.name = "is_answer_correct_" + newAnswerIndex;
            checkbox.id = "correct_answer_checkbox_" + newAnswerIndex;

            // create 'correct'-text for checkbox
            var span = document.createElement("span");
            span.innerHTML = " correct";

            // create remove button
            var removeButton = document.createElement("input");
            removeButton.type = "image";
            removeButton.className = "remove_icon";
            removeButton.src="{{ (url('/remove_icon.png'))}}";
            removeButton.addEventListener('click', function() {
                removeQuestion(newAnswerIndex);
            }, false);

            div.appendChild(checkbox);
            div.appendChild(span);
            div.appendChild(removeButton);

            // create textarea
            var textarea = document.createElement("textarea");
            textarea.rows = "1";
            textarea.cols = "50";
            textarea.className = "form-control";
            textarea.id = "possible_answer_" + newAnswerIndex;
            textarea.name = "possible_answer_" + newAnswerIndex;

            // container of a one answer
            var answer_container = document.createElement("div");
            answer_container.id = "answer_container_" + newAnswerIndex;

            // add to container
            answer_container.appendChild(label);
            answer_container.appendChild(div);
            answer_container.appendChild(textarea);
            answer_container.appendChild(document.createElement("br"));

            // container of all answers
            container_possible_answers.appendChild(answer_container);
        }

        /**
         * Remove a possible answer when clicking on remove button.
         **/
        function removeQuestion(index) {
            var container_possible_answers = document.getElementById("container_possible_answers");
            var answer = document.getElementById("answer_container_" + index);
            container_possible_answers.removeChild(answer);
            updateAnswerIndexes();
        }

        /**
         * This function is called after delete button of an answer is clicked.
         * It will set the question again in a proper order [1, 2, 3, 4, ....].
         **/
        function updateAnswerIndexes() {
            // Number of current possible answers
            var actualNumberOfPossibleAnswers = document.getElementById("container_possible_answers").childElementCount;

            // Container <div> where dynamic content will be placed
            var containerPossibleAnswers = document.getElementById("container_possible_answers");

            for (var x = 1; x < actualNumberOfPossibleAnswers + 1; x++) {

                // Container <div> where dynamic content will be placed
                var answerContainer = containerPossibleAnswers.children[x - 1];
                answerContainer.id = "answer_container_" + x;

                // update label indexes
                var label = answerContainer.getElementsByTagName("label")[0];
                label.id = "correct_answer_" + x;
                label.for = "correct_answer";
                label.innerHTML = "Answer " + x;

                // update checkbox indexes
                var div = answerContainer.getElementsByTagName("div")[0];
                var checkbox = div.getElementsByTagName("input")[0];
                checkbox.name = "is_answer_correct_" + x;
                checkbox.id = "correct_answer_checkbox_" + x;

                // update textare indexes
                var textarea = answerContainer.getElementsByTagName("textarea")[0];
                textarea.id = "possible_answer_" + x;
                textarea.name = "possible_answer_" + x;

            }
        }

        function validateQuestions() {
            <!-- This function checks if at least one question in multiple choice mode is set to correct.-->
            <!-- It also checks if an empty text field is set to correct. -->
            var correct_answers = 0;
            var empty_answer = false;
            var actualNumberOfPossibleAnswers = document.getElementById("container_possible_answers").childElementCount;

            for (var x = 1; x < actualNumberOfPossibleAnswers + 1; x++) {
                var possible_answer = document.getElementById('possible_answer_' + x).value;
                var is_checkbox_checked = document.getElementById('correct_answer_checkbox_' + x).checked;
                if (possible_answer.trim().length == 0) {
                    empty_answer = true;
                }
                if (possible_answer.trim().length > 0 && is_checkbox_checked) {
                    correct_answers += 1;
                }
            }

            if (correct_answers == 0) {
                document.getElementById("error-message").innerHTML = "You have to set at least one correct answer."
                return false;
            }
            if (empty_answer) {
                document.getElementById("error-message").innerHTML = "You can not have an empty answer."
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
@endsection
