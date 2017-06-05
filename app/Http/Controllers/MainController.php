<?php

namespace App\Http\Controllers;

use App\Chapter;
use App\Lecture;
use App\Survey;
use Illuminate\Support\Facades\DB;
use Barryvdh\Debugbar\Facade as Debugbar;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Created by PhpStorm.
 * User: User
 * Date: 15.05.2017
 * Time: 20:36
 */

class MainController extends Controller
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
     * This function checks if a user is logged in.
     *
     * @return bool true if user is authenticated, false if not.
     */
    private function isAuthenticated() {
        $user = Auth::user();
        return isset($user);
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

        // a list with objects of type lecture.
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
                                if ($lecture_of_results->getId() == $lecture['id']) {

                                    // if chapter already exists -> only a new survey has to be added to the chapter.
                                    if ($lecture_of_results->check_if_chapter_exists($chapter['id'])) {
                                        $result_survey = new Survey($survey['id'], $survey['name'], $chapter['id'], null);
                                        $lecture_of_results->getChapterById($chapter['id'])->addSurvey($result_survey);
                                        break 2;    // break 2 loops -> continue with nex survey of $all_surveys

                                    // if chapter does not exist in this lecture -> create new chapter with survey and
                                    // add it to existing lecture in result list.
                                    } else {
                                        $result_survey = new Survey($survey['id'], $survey['name'], $chapter['id'], null);
                                        $result_chapter = new Chapter($chapter['id'], $chapter['name'], $result_survey);
                                        $lecture_of_results->addChapter($result_chapter);
                                        break 2;    // break 2 loops -> continue with nex survey of $all_surveys
                                    }
                                }
                            }

                            // if lecture does not exists in our result list -> create a new lecture and add it to result list.
                            $result_survey = new Survey($survey['id'], $survey['name'], $chapter['id'], null);
                            $result_chapter = new Chapter($chapter['id'], $chapter['name'], $result_survey);
                            $result_lecture = new lecture($lecture['id'], $lecture['name'], $result_chapter);
                            $result[] = $result_lecture;
                            break;
                        }
                    }
                }
            }
        }

        Debugbar::info($result);
        return view('main', ['lectures' => $result]);
    }

    /**
     * This function creates a new chapter entry into DB.
     *
     * @param $lecture_name chapter title of the chapter the user wants to create.
     * @return redirect to new created chapter view.
     */
    public function createNewLecture($lecture_name) {
        $user = Auth::user();
        if ($this->isAuthenticated()) {
            $new_lecture_id = DB::table('lecture')->insertGetId(['name' => $lecture_name, 'user_id' => $user['id']]);
            return redirect()->route('lecture', ['lecture_id' => $new_lecture_id]);
        } else {
            // TODO: Permission denied page.
            print "Permission denied";
        }
    }

    /**
     * This function is called when user clicks Remove-Button in main-view in order to remove lectures.
     * Chapters will be removed from DB and will be redirected to same page (main-view).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeLectures() {
        $request_parameter = (array) Request::all();
        $lecture_to_remove_array = explode("_", $request_parameter['lectures_to_remove']);

        //print_r($lecture_to_remove_array);

        if ($this->isAuthenticated()) {
            DB::transaction(function() use ($lecture_to_remove_array) {

                // delete lecture entry.
                // delete all chapter entries which belong to that lecture.
                for ($x = 0; $x < sizeof($lecture_to_remove_array); $x++) {
                    DB::table('lecture')->where(['id' => $lecture_to_remove_array[$x]])->delete();
                    DB::table('chapter')->where(['lecture_id' => $lecture_to_remove_array[$x]])->delete();
                }

                // delete all surveys which have a chapter_id which is no longer available in table chapter.
                $all_surveys = DB::table('survey')->select('chapter_id')->get();
                foreach ($all_surveys as $survey) {
                    $chapter_exists =  sizeof(DB::table('chapter')->where(['id' => $survey->chapter_id])->get()) > 0;
                    if (!$chapter_exists) {
                        DB::table('survey')->where(['chapter_id' => $survey->chapter_id])->delete();
                    }
                }

                // delete all questions which have a survey_id which is no longer available in table survey.
                $all_questions = DB::table('questions')->select('survey_id')->get();
                foreach ($all_questions as $question) {
                    $survey_exists =  sizeof(DB::table('survey')->where(['id' => $question->survey_id])->get()) > 0;
                    if (!$survey_exists) {
                        DB::table('questions')->where(['survey_id' => $question->survey_id])->delete();
                    }
                }
            });
        } else {
            // TODO: Permission denied page
            print "Permission denied";
        }

        return redirect()->route('lectures');
    }
}