<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Barryvdh\Debugbar\Facade as Debugbar;

/**
 * Created by PhpStorm.
 * User: User
 * Date: 15.05.2017
 * Time: 20:36
 */

class LectureController extends Controller
{
    var $select_all_surveys_of_prof = 'SELECT survey.id AS survey_id, survey.name AS survey_name, chapter.id AS chapter_id, chapter.name AS chapter_name, lecture.id AS lecture_id, lecture.name AS lecture_name FROM survey INNER JOIN chapter INNER JOIN lecture ON survey.prof_id=1 AND survey.chapter_id=chapter.id AND chapter.lecture_id=lecture.id';

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * TODO müssen so sortiert werden: lecture_id -> chapter_id -> survey_id
     *
     * @return Response
     */
    public function show_lectures()
    {
        $surveys = DB::select($this->select_all_surveys_of_prof);
        Debugbar::info($surveys);
        return view('main');
    }
}

