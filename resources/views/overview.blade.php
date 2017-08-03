@extends('user_dashboard')

@section('dashboard_content')
    <main>
        @if (Auth::guest())
            @yield('content')
        @else

            <div id="topic">Overview</div>

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
        @endif
    </main>
@endsection