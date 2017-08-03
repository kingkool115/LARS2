@extends('user_dashboard')

@section('dashboard_content')
<main>
    @if (Auth::guest())
        @yield('content')
    @else
        <div id="topic">
            <button id="edit_lecture_button" type="submit" class="btn btn-primary" onclick="switchEditMode();">Rename</button>
            <span id="lecture_name_label">{{$lecture->name}}</span>
            <span><input id="lecture_name_edit" type="text" value="{{$lecture->name}}" style="display: none"></span>
        </div>

        <table class="chapters_table">
            <tr id="very_first_table_row">
                <th>Chapter Name</th>
                <th>
                    <div id="remove-label">Remove</div>
                    <button id="remove-button" type="submit" class="btn btn-primary" style="background-color: #a94442; display: none;" onclick="removeChapters();"> Remove </button>
                </th>
            </tr>
            <tr class="survey_table_content">
                <th></th>
                <th></th>
            </tr>
            @foreach($all_chapters as $chapter)
                <tr class="survey_table_content">
                    <th>
                        <a href="{{route('chapter', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter->id])}}">
                            {{ $chapter->name}}
                        </a>
                    </th>
                    <th class="remove-checkboxes">
                        <input id="chapter_to_remove_{{$chapter->id}}" autocomplete="off" type="checkbox" name="chapter-to-remove" onchange="displayHideRemoveButton();">
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

    /**
     * Edit Button is clicked.
     **/
    function switchEditMode() {
        var lecture_name_label = document.getElementById('lecture_name_label');
        var lecture_name_edit = document.getElementById('lecture_name_edit');
        var edit_lecture_button = document.getElementById('edit_lecture_button');

        if (lecture_name_label.style.display == 'none') {
            lecture_name_label.style.display = 'inline'
            lecture_name_edit.style.display = 'none';
            edit_lecture_button.innerHTML = 'Edit'
            var new_lecture_name = lecture_name_edit.value;
            var url = '{{route('rename_lecture', ['lecture' => $lecture_id, 'new_lecture_name' => 'new_name'])}}';
            url = url.replace('new_name', new_lecture_name);
            window.location.href = url;
        } else {
            edit_lecture_button.innerHTML = 'Save'
            lecture_name_label.style.display = 'none'
            lecture_name_edit.style.display = 'inline';
        }
    }
</script>
@endsection