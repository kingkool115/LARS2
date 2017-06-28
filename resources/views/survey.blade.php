@extends('user_dashboard')

@section('dashboard_content')
<main>
    @if (Auth::guest())
        @yield('content')
    @else
        <div id="topic">
            <a href="{{route('chapter', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id])}}">Chapter {{$chapter->name}}</a><br>
            {{$survey->name}}
        </div>

        <table class="questions_table">
            <tr id="very_first_table_row">
                <th id="question_column">Question</th>
                <th>
                    <div id="remove-label">Remove</div>
                    <button id="remove-button" type="submit" class="btn btn-primary" style="background-color: #a94442; display: none;" onclick="removeQuestions();"> Remove </button>
                </th>
            </tr>
            <tr class="survey_table_content">
                <th></th>
                <th></th>
            </tr>
        @foreach($all_questions as $question)
            <tr class="survey_table_content">
                <th>
                    <a href="{{route('question', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $question->survey_id, 'question_id' => $question->id])}}">
                        {{ $question->question}}
                    </a>
                </th>
                <th class="remove-checkboxes">
                    <input id="question_to_remove_{{$question->id}}" autocomplete="off" type="checkbox" name="question-to-remove" onchange="displayHideRemoveButton();">
                </th>
            </tr>
        @endforeach
        </table>

            <table id="textfield-new-slide">
                <tr>
                    <th>
                        <button type="submit" class="btn btn-primary" onclick="createNewQuestion()">Create question</button>
                    </th>
                </tr>
            </table>
            <script>

                /**
                 * This function is called when Create-Button is clicked and will redirect you to editQuestion-view.
                 */
                function createNewQuestion() {
                    var url = "{{route('question', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id, 'question_id' => 0])}}";
                    window.location.href = url;
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
                    var questions_to_remove = "";
                    for (x = 0; x < all_checkboxes.length; x++) {
                        if (all_checkboxes[x].checked) {
                            var checkbox_id = all_checkboxes[x].id;
                            var slide_number = checkbox_id.split("question_to_remove_")[1];
                            questions_to_remove += slide_number + '_';
                        }
                    }
                    // remove last underline character
                    questions_to_remove = questions_to_remove.slice(0, -1);
                    var url = '{{route('remove_questions', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id])}}';
                    // put slides_to_remove as parameter into the url
                    window.location.href = url + "?questions_to_remove=" + questions_to_remove;
                }
            </script>
        @endif
</main>
@endsection
