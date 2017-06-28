@extends('user_dashboard')

@section('dashboard_content')
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
                                <li><a href={{route('survey', ['lecture_id' => $lectures[$x]->getId(), 'chapter_id' => $survey->getChapterId(), 'survey_id' => $survey->getId()])}}>{{ $survey->getName() }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                        @endfor
                    </ul>
                </li>
                @endfor
            </ul>
        </div>
        <br><br><br><br><br><br>

        <table class="lectures_table">
            <tr id="very_first_table_row">
                <th>Chapter Name</th>
                <th>
                    <div id="remove-label">Remove</div>
                    <button id="remove-button" type="submit" class="btn btn-primary" style="background-color: #a94442; display: none;" onclick="removeLectures();"> Remove </button>
                </th>
            </tr>
            @for ($x = 0; $x < 1; $x++)
                <tr class="lecture_table_content">
                    <th></th>
                    <th></th>
                </tr>
            @endfor
            @foreach($lectures as $lecture)
                <tr class="lecture_table_content">
                    <th>
                        <a href="{{route('lecture', ['lecture_id' => $lecture->getId()])}}">
                            {{ $lecture->getName()}}
                        </a>
                    </th>
                    <th class="remove-checkboxes">
                        <input id="lecture_to_remove_{{$lecture->getId()}}" autocomplete="off" type="checkbox" name="lecture-to-remove" onchange="displayHideRemoveButton();">
                    </th>
                </tr>
            @endforeach
        </table>

        <table id="textfield-new-lecture">
            <tr>
                <th><input type="text" name="new_lecture" placeholder="Enter lecture name"></th>
                <th>
                    <button type="submit" class="btn btn-primary" onclick="createNewLecture()">Create lecture</button>
                </th>
            </tr>
        </table>
    @endif
</main>

<script>

    /**
     * This function creates a new lecture if Create-Button is clicked.
     */
    function createNewLecture() {
        // TODO: check if lecture name not empty
        var lecture_name = document.getElementsByName("new_lecture")[0].value;
        var url = "{{route('create_lecture', ['lecture_name' => 'new_lecture_name'])}}";
        url = url.replace('new_lecture_name', lecture_name);
        window.location.href = url;
    }

    /**
     * This function will display Remove Button when at least one checkbox is checked.
     * Otherwise there is just a label visible.
     */
    function displayHideRemoveButton() {
        var all_checkboxes = document.getElementsByName('lecture-to-remove');
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
     * It detects which lectures are marked to be removed and concat them to a string.
     * This string of concatenated lecture ids will be passed as parameter to the URL which handles the deletion.
     */
    function removeLectures() {
        var all_checkboxes = document.getElementsByName('lecture-to-remove');
        var lectures_to_remove = "";
        for (x = 0; x < all_checkboxes.length; x++) {
            if (all_checkboxes[x].checked) {
                var checkbox_id = all_checkboxes[x].id;
                var lecture_id = checkbox_id.split("lecture_to_remove_")[1];
                lectures_to_remove += lecture_id + '_';
            }
        }
        // remove last underline character
        lectures_to_remove = lectures_to_remove.slice(0, -1);
        var url = '{{route('remove_lectures')}}';
        // put slides_to_remove as parameter into the url
        window.location.href = url + "?lectures_to_remove=" + lectures_to_remove;
    }
</script>
@endsection