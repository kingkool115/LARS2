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
        <div id="topic">Welcome to your QUESTION page!!</div>
    @endif
    <form>
        <input type="radio" name="question-type" value="multiple-choice">Multiple choice question<br>
        <input type="radio" name="question-type" value="text-response">Text respone question
    </form>

        <form id="upload-image-row" action="/upload_image" method="post" enctype="multipart/form-data">
            <div id="question-form" class="form-group">
                <!-- Question -->
                <label for="question" class="col-md-4 control-label">Question</label>
                <textarea rows="4" cols="50" class="form-control" name="question" required autofocus></textarea>

                <!-- Image Upload -->
                {{ csrf_field() }}
                <label id="image-upload" for="question-image" class="col-md-4 control-label">Select image</label><br/>
                <input type="file" name="question-image" id="fileToUpload">

                <!-- Correct answer -->
                <label id="correct-answer" for="correct_answer" class="col-md-4 control-label">Correct answer</label>
                <input type="text" class="form-control" name="correct_answer">

                <!-- Submit Button -->
                <button id="submit-question-button"class="btn btn-primary" type="submit"> Create Question</button>
            </div>
        </form>
</main>

</body>
</html>
