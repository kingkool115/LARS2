<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 23.05.2017
 * Time: 16:15
 */

namespace App\Http\Controllers;

use Barryvdh\Debugbar\Facade as Debugbar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use \Illuminate\Support\Facades\Auth;

class SurveyController extends Controller
{

    private $lecture_id;
    private $chapter_id;

    /**
     * Create a new controller instance.
     *
     * @return void
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
    private function surveyExists($survey_id){
        return sizeof(DB::table('survey')->select('id', 'name')->where('id', $survey_id)->get()) > 0;
    }

    /**
     * This function is called for GET: lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/
     * It show's all the questions of a certain survey.
     *
     * @param $lecture_id id of the lecture this survey belongs to.
     * @param $chapter_id id of the chapter this survey belongs to.
     * @param $survey_id id of survey that the view shows us.
     * @return a overview of all questions belonging to this survey.
     */
    public function showQuestions($lecture_id, $chapter_id, $survey_id)
    {
        $this->lecture_id = $lecture_id;
        $this->chapter_id = $chapter_id;

        Debugbar::warning("fubaaa" . $chapter_id);
        // TODO: check if survey belongs to correct professor.
        if ($this->hasPermission($lecture_id)) {
            if ($this->surveyExists($survey_id)) {
                $all_questions = DB::table('questions')->select('id', 'survey_id', 'question', 'slide_number')->where(['survey_id' => $survey_id])->orderBy('slide_number')->get();
                $survey = (array)DB::table('survey')->select('id', 'name')->where('id', $survey_id)->get()[0];

                // TODO: kann man bestimmt schöner machen
                $chapter_name = (array)DB::table('chapter')->select('name')->where('id', $chapter_id)->get()[0];

                $result = [];
                foreach ($all_questions as $question) {
                    $result[] = (array)$question;
                }

                //Debugbar::warning($result);
                return view('survey', compact('result', 'survey', 'chapter_id', 'chapter_name', 'lecture_id', 'survey_id'));
            } else {
                // TODO: survey does not exist page.
                print "Sorry, but your requested survey does not exist.";
            }
        } else {
            // TODO: permission denied page.
            print "Permission denied";
        }
    }

    /**
     * This function is called when user clicks Remove-Button in survey-view in order to remove questions.
     * Questions will be removed from DB and will be redirect to same page (survey-view).
     *
     * @param $lecture_id id of the lecture this survey belongs to.
     * @param $chapter_id id of the chapter this survey belongs to.
     * @param $survey_id id of survey that the view shows us.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeQuestions($lecture_id, $chapter_id, $survey_id) {
        $request_parameter = (array) Request::all();
        $slide_to_remove_array = explode("_", $request_parameter['slides_to_remove']);

        //print_r($slide_to_remove_array);

        if ($this->hasPermission($lecture_id)) {
            DB::transaction(function() use ($slide_to_remove_array, $survey_id) {
                for ($x = 0; $x < sizeof($slide_to_remove_array); $x++) {
                    DB::table('questions')->where(['survey_id' => $survey_id, 'slide_number' => $slide_to_remove_array[$x]])->delete();
                }
            });
        } else {
            // TODO: Permission denied page
            print "Permission denied";
        }

        return redirect()->route('survey', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id]);
    }

    /**
     * Check if a survey already has a question for a certain slide number.
     *
     * @param $lecture_id id of the lecture this survey belongs to. Unused but needed for url resolving.
     * @param $chapter_id id of the chapter this survey belongs to. Unused but needed for url resolving.
     * @param $slide_number check if this slide number has already a question.
     * @return true if survey has for the given slide number a question, else false.
     **/
    public function slideNumberExists($lecture_id, $chapter_id, $survey_id, $slide_number) {
        $slide_number_exists =  DB::table('questions')->where('survey_id', $survey_id)->where('slide_number', $slide_number)->get()->count() > 0;
        Debugbar::info($slide_number_exists );
        return response()->json([
            'slideNumberExists' => $slide_number_exists,
        ]);
    }


}