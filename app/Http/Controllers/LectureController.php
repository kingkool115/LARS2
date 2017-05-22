<?php

namespace App\Http\Controllers;

use App\Chapter;
use App\Lecture;
use App\Survey;
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
        //$surveys = DB::select($this->select_all_surveys_of_prof);
        //
        $all_lectures = DB::table('lecture')->select('id', 'name')->get();
        $all_chapters = DB::table('chapter')->select('id', 'name', 'lecture_id')->get();
        $all_surveys = DB::table('survey')->select('id', 'name', 'chapter_id')->get();

        // a list with objects of type Lecture.
        $result = [];

        // iterate through all surveys of DB
        for ($x = 0; $x < sizeof($all_surveys); $x++) {
            $survey = (array)$all_surveys[$x];

            // iterate through all chapters of DB
            for ($y = 0; $y < sizeof($all_chapters); $y++) {
                $chapter = (array)$all_chapters[$y];

                // if survey belongs to chapter
                if ($survey['chapter_id'] == $chapter['id']) {

                    // iterate through all lectures of DB
                    for ($z = 0; $z < sizeof($all_lectures); $z++) {
                        $lecture = (array)$all_lectures[$z];

                        // if chapter belongs to lecture of DB.
                        if ($chapter['lecture_id'] == $lecture['id']) {

                            // iterate through $result
                            foreach ($result as $lecture_of_results) {
                                
                                // if lecture already exists in our result list, then just
                                if ($lecture_of_results.getId() == $lecture['id']) {

                                    // if chapter already exists -> only a new survey has to be added to the chapter.
                                    if ($lecture_of_results.check_if_chapter_exists(chapter['id'])) {
                                        $result_survey = new Survey($survey['id'], $survey['name'], null);
                                        $lecture_of_results.getChapterById(chapter['id'].addSurvey($result_survey));
                                        break 2;    // break 3 loops -> continue with nex survey of $all_surveys

                                    // if chapter does not exist in this lecture -> create new chapter with survey and
                                    // add it to existing lecture in result list.
                                    } else {
                                        $result_survey = new Survey($survey['id'], $survey['name'], null);
                                        $result_chapter = new Chapter($chapter['id'], $chapter['name'], $result_survey);
                                        $lecture_of_results.addChapter($result_chapter);
                                        break 2;    // break 3 loops -> continue with nex survey of $all_surveys
                                    }
                                }
                            }

                            // if lecture does not exists in our result list -> create a new lecture and add it to result list.
                            $result_survey = new Survey($survey['id'], $survey['name'], null);
                            $result_chapter = new Chapter($chapter['id'], $chapter['name'], $result_survey);
                            $result_lecture = new Lecture($lecture['id'], $lecture['name'], $result_chapter);
                            $result[] = $result_lecture;
                            break;
                        }
                    }
                }
            }
            Debugbar::info($result);
        }
        //$survey = (array)$all_surveys;
        //Debugbar::info($survey);
        return view('main');
    }

    /**
     * @param $lecture_array
     * @param $lecture
     * @param $chapter
     */
    private function check_if_already_exists($lecture_array, $chapter_id) {
        foreach ($lecture_array as $lecture) {
            if ($lecture.check_if_chapter_exists($chapter_id)) {

            }
        }
    }
/*
echo $lectures;
[{"id":1,"name":"Lineare Algebra I"},{"id":2,"name":"Einf\u00fchrung in die Informatik"}]⏎
>>> echo $chapters;
[{"id":1,"name":"Summieren","lecture_id":1},{"id":2,"name":"Subtrahieren","lecture_id":1},{"id":3,"name":"Hello World","lecture_id":2},{"id":4,"name":"Primitive Datentypen","lecture_id":2}]⏎
>>> echo $surveys;
[{"id":1,"name":"Fragen \u00fcber das Kapitel \"Summieren\"","chapter_id":1},{"id":2,"name":"Fragen \u00fcber Kapitel \"Subtrahieren\"","chapter_id":2},{"id":3,"name":"Fragen \u00fcber das Kapitel \"Hello World\"","chapter_id":3}]⏎

*/}

