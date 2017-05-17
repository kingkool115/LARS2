
<!DOCTYPE html>
<html lang="de" class="no-js">
<head>

    <meta charset="utf-8">

    <meta name="language" content="de">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

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

    <div id="topic">Top Navigation Example</div>

    <div class="css-treeview">
        <ul>
            <!-- 1st level -->
            <li><input type="checkbox" id="item-0" /><label for="item-0">This Folder is Closed By Default</label>
                <ul>
                    <!-- 2nd level -->
                    <li><input type="checkbox" id="item-0-0" /><label for="item-0-0">Ooops! A Nested Folder</label>
                        <ul>
                            <!-- 3rd Level -->
                            <li><a href="./">Item 1</a></li>
                            <li><a href="./">Item 2</a></li>
                            <li><a href="./">Item 3</a></li>
                        </ul>
                    </li>
                    <!-- 2nd level -->
                    <li><input type="checkbox" id="item-0-1" /><label for="item-0-1">Yet Another One</label>
                        <ul>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                        </ul>
                    </li>
                    <li><input type="checkbox" id="item-0-2" disabled="disabled" /><label for="item-0-2">Disabled Nested Items</label>
                        <ul>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                        </ul>
                    </li>
                </ul>
            </li>


            <li><input type="checkbox" id="item-1" checked="checked" /><label for="item-1">This One is Open by Default...</label>
                <ul>
                    <li><input type="checkbox" id="item-1-0" /><label for="item-1-0">And Contains More Nested Items...</label>
                        <ul>
                            <li><a href="./">Look Ma - No Hands</a></li>
                            <li><a href="./">Another Item</a></li>
                            <li><a href="./">And Yet Another</a></li>
                        </ul>
                    </li>
                    <li><a href="./">Lorem</a></li>
                    <li><a href="./">Ipsum</a></li>
                    <li><a href="./">Dolor</a></li>
                    <li><a href="./">Sit Amet</a></li>
                </ul>
            </li>
            <li><input type="checkbox" id="item-2" /><label for="item-2">Can You Believe...</label>
                <ul>
                    <li><input type="checkbox" id="item-2-0" /><label for="item-2-0">That This Treeview...</label>
                        <ul>
                            <li><input type="checkbox" id="item-2-2-0" /><label for="item-2-2-0">Does Not Use Any JavaScript...</label>
                                <ul>
                                    <li><a href="./">But Relies Only</a></li>
                                    <li><a href="./">On the Power</a></li>
                                    <li><a href="./">Of CSS3</a></li>
                                </ul>
                            </li>
                            <li><a href="./">Item 1</a></li>
                            <li><a href="./">Item 2</a></li>
                            <li><a href="./">Item 3</a></li>
                        </ul>
                    </li>
                    <li><input type="checkbox" id="item-2-1" /><label for="item-2-1">This is a Folder With...</label>
                        <ul>
                            <li><a href="./">Some Nested Items...</a></li>
                            <li><a href="./">Some Nested Items...</a></li>
                            <li><a href="./">Some Nested Items...</a></li>
                            <li><a href="./">Some Nested Items...</a></li>
                            <li><a href="./">Some Nested Items...</a></li>
                        </ul>
                    </li>
                    <li><input type="checkbox" id="item-2-2" disabled="disabled" /><label for="item-2-2">Disabled Nested Items</label>
                        <ul>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                            <li><a href="./">item</a></li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    @endif
</main>


</body>
</html>