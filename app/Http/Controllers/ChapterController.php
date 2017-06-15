<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Barryvdh\Debugbar\Facade as Debugbar;


class ChapterController extends Controller {

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * This function checks if a survey with given survey id exists in DB.
     *
     * @param $survey_id id of the survey to check.
     * @return bool true, if survey exists in DB. Else false.
     */
    private function chapterExists($chapter_id){
        return sizeof(DB::table('chapter')->where('id', $chapter_id)->get()) > 0;
    }

    /**
     * This function handles route lecture/{lecture_id}/chapter/{chapter_id}/surveys.
     * It gives an overview over all surveys that belong to a chapter.
     *
     * @param $lecture_id is the id of the lecture this chapter belongs to.
     * @param $chapter_id is the id of the chapter the surveys belong to.
     * @return chapter.blade.php
     */
    public function showSurveys($lecture_id, $chapter_id) {

        Debugbar::warning($chapter_id);
        if ($this->hasPermission($lecture_id)) {
            if ($this->chapterExists($chapter_id)) {
            $all_surveys = DB::table('survey')->select('id', 'name')->where(['chapter_id' => $chapter_id])->get();
            $chapter = (array)DB::table('chapter')->select('id', 'name')->where(['id' => $chapter_id])->get()[0];

            // TODO: kann man bestimmt schöner machen
            $lecture_name = (array)DB::table('lecture')->select('name')->where('id', $lecture_id)->get()[0];

            $result = [];
            foreach ($all_surveys as $survey) {
                $result[] = (array)$survey;
            }

            Debugbar::warning($result);
            return view('chapter', compact('result', 'chapter', 'lecture_name', 'lecture_id', 'chapter_id'));
            } else {
                // TODO: chapter does not exist page.
                print "Sorry, but your requested chapter does not exist.";

            }
        } else {
            // TODO: Permission denied page.
            print "Permission denied.";
        }
    }

    /**
     * This function creates a new survey entry into DB.
     *
     * @param $lecture_id id of lecture this chapter belongs to.
     * @param $chapter_id id of chapter the new survey belongs to.
     * @param $survey_name survey title of the survey the user wants to create.
     * @return redirect to new created survey view.
     */
    public function createNewSurvey($lecture_id, $chapter_id, $survey_name) {
        if ($this->hasPermission($lecture_id)) {
            $new_survey_id = DB::table('survey')->insertGetId(['chapter_id' => $chapter_id, 'name' => $survey_name]);
            return redirect()->route('survey', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $new_survey_id]);
        } else {
            // TODO: Permission denied page.
            print "Permission denied";
        }
    }

    /**
     * This function is called when user clicks Remove-Button in chapter-view in order to remove surveys of that chapter.
     * Surveys will be removed from DB and will be redirected to same page (chapter-view).
     *
     * @param $lecture_id id of the lecture this survey belongs to.
     * @param $chapter_id id of the chapter this survey belongs to.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeSurveys($lecture_id, $chapter_id) {
        $request_parameter = (array) Request::all();
        $survey_to_remove_array = explode("_", $request_parameter['surveys_to_remove']);

        //print_r($survey_to_remove_array);

        if ($this->hasPermission($lecture_id)) {
            DB::transaction(function() use ($survey_to_remove_array) {
                for ($x = 0; $x < sizeof($survey_to_remove_array); $x++) {
                    DB::table('survey')->where(['id' => $survey_to_remove_array[$x]])->delete();
                    DB::table('questions')->where(['survey_id' => $survey_to_remove_array[$x]])->delete();
                }
            });
        } else {
            // TODO: Permission denied page
            print "Permission denied";
        }

        return redirect()->route('chapter', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id]);
    }
}