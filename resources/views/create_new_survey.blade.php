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
            <a href="{{route('show_create_survey_form')}}">Create new survey</a>
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
        </div>

        <!-- Radio Buttons to select type how to create new survey -->
        <form onclick="displayCorrectForm()">
            <input type="radio" id="create-new-lecture-radio" name="survey-type" checked>Create new survey for a new lecture<br>
            <input type="radio" id="create-new-chapter-to-existing-lecture-radio" name="survey-type">Create new survey for an existing lecture<br>
            <input type="radio" id="create-new-survey-to-existing-chapter-radio" name="survey-type">Create new survey for an existing chapter
        </form>
        <br>
        <br>
        <br>
        <br>

        <!-- Form to create a new lecture -->
        <form id="create-new-lecture-form" action=""
              method="post" enctype="multipart/form-data" autocomplete="off"> <!-- when page is reloaded, than reload form from DB.  -->
            <div class="form-group question-form">
                <!-- Lecture Name -->
                <label for="lecture-new-lecture">Lecture</label>
                <input type="text" class="form-control" name="lecture-new-lecture" required><br>

                <!-- Chapter Name -->
                <label for="chapter-new-lecture">Chapter</label>
                <input type="text" class="form-control" name="chapter-new-lecture" required><br>

                <!-- Survey Name -->
                <label for="survey-new-lecture">Survey</label>
                <input type="text" class="form-control" name="survey-new-lecture" required><br>

                <!-- Submit Button -->
                <button id="submit-new-lecture-button"class="btn btn-primary" type="submit">
                    Create new Survey
                </button>
            </div>
        </form>

        <!-- Form to create a new chapter -->
        <form id="create-new-chapter-to-existing-lecture-form" action=""
              method="post" enctype="multipart/form-data" autocomplete="off"> <!-- when page is reloaded, than reload form from DB.  -->
            <div class="form-group question-form">
                <!-- Lecture Dropdown -->
                <label for="lecture-dropdown-new-chapter">Lecture</label><br>
                <select name="lecture-dropdown-new-chapter">
                </select><br><br>

                <!-- Chapter Name -->
                <label for="chapter-new-chapter">Chapter</label>
                <input type="text" class="form-control" name="chapter-new-chapter" required><br>

                <!-- Survey Name -->
                <label for="survey-new-chapter">Survey</label>
                <input type="text" class="form-control" name="survey-new-chapter" required><br>

                <!-- Submit Button -->
                <button id="submit-new-chapter-button"class="btn btn-primary" type="submit">
                    Create new Survey
                </button>
            </div>
        </form>

        <!-- Form to create a new survey -->
        <form id="create-new-survey-to-existing-chapter-form" action=""
              method="post" enctype="multipart/form-data" autocomplete="off"> <!-- when page is reloaded, than reload form from DB.  -->
            <div class="form-group question-form">
                <!-- Lecture Dropdown -->
                <label for="lecture-dropdown-new-survey">Lecture</label><br>
                <select name="lecture-dropdown-new-survey">
                    @for ($x = 0; $x < count($lectures); $x++)
                        <option value="{{$lectures[$x]->getId()}}">{{$lectures[$x]->getName()}}</option>
                    @endfor
                </select><br><br>

                <!-- Chapter Dropdown -->
                <label for="chapter-dropdown-new-survey">Chapter</label><br>
                <select name="chapter-dropdown-new-survey">
                </select><br><br>

                <!-- Survey Name -->
                <label for="survey-new-survey">Survey</label>
                <input type="text" class="form-control" name="survey-new-survey" required><br>

                <!-- Submit Button -->
                <button id="submit-new-survey-button"class="btn btn-primary" type="submit">
                    Create new Survey
                </button>
            </div>
        </form>

        <div id="error-message"></div>
    @endif
</main>

<script>

    /**
     * this function helps to display either text response form OR multiple choice form -->
     */
    function displayCorrectForm() {
        var create_new_lecture_radio = document.getElementById('create-new-lecture-radio');
        var create_new_chapter_to_existing_lecture_radio = document.getElementById('create-new-chapter-to-existing-lecture-radio');
        var create_new_survey_radio_to_existing_chapter_radio = document.getElementById('create-new-survey-to-existing-chapter-radio');

        var create_new_lecture_form = document.getElementById('create-new-lecture-form');
        var create_new_chapter_to_existing_lecture_form = document.getElementById('create-new-chapter-to-existing-lecture-form');
        var create_new_survey_radio_to_existing_chapter_form = document.getElementById('create-new-survey-to-existing-chapter-form');

        // display create new lecture form
        if (create_new_lecture_radio.checked) {
            create_new_lecture_form.style.display = 'initial';
            create_new_chapter_to_existing_lecture_form.style.display = 'none';
            create_new_survey_radio_to_existing_chapter_form.style.display = 'none';
        }

        // display create new chapter of existing lecture form
        else if (create_new_chapter_to_existing_lecture_radio.checked) {
            create_new_lecture_form.style.display = 'none';
            create_new_chapter_to_existing_lecture_form.style.display = 'initial';
            create_new_survey_radio_to_existing_chapter_form.style.display = 'none';
        }

        // display create new survey of existing chapter form
        else {
            create_new_lecture_form.style.display = 'none';
            create_new_chapter_to_existing_lecture_form.style.display = 'none';
            create_new_survey_radio_to_existing_chapter_form.style.display = 'initial';
        }
    };
    displayCorrectForm();

    function fillDropDownList() {
        var select = document.getElementsByName("lecture-dropdown-new-chapter")[0];

        @for ($x = 0; $x < count($lectures); $x++)
            var el = document.createElement("option");
            el.textContent = "{{$lectures[$x]->getName()}}";
            el.valueOf = "{{$lectures[$x]->getId()}}";
            select.appendChild(el);
        @endfor
    }
    fillDropDownList();
</script>

</body>
</html>