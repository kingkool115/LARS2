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
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function hasPermission($user_id, $survey_id) {
        return sizeof(DB::table('survey')->where(['user_id' => $user_id, 'id' => $survey_id])->get()) > 0;
    }

    /**
     * This function is called for GET: /survey/{survey_id}/
     * It show's all the questions of a certain survey.
     *
     * @param $survey_id id of survey that the view shows us.
     * @return a overview of all questions belonging to this survey.
     */
    public function showQuestions($survey_id)
    {
        $user = Auth::user();

        // TODO: check if survey belongs to correct professor.
        if ($this->hasPermission($user['id'], $survey_id)) {
            $all_questions = DB::table('questions')->select('id', 'survey_id', 'question', 'slide_number')->where(['survey_id' => $survey_id])->orderBy('slide_number')->get();
            $survey = (array)DB::table('survey')->select('id', 'name')->where('id', $survey_id)->get()[0];

            $chapter_id = (array)DB::table('survey')->select('chapter_id')->where('id', $survey_id)->get()[0];
            $chapter_name = (array)DB::table('chapter')->select('name')->where('id', $chapter_id)->get()[0];

            $result = [];
            foreach ($all_questions as $question) {
                $result[] = (array)$question;
            }

            Debugbar::warning($result);
            return view('survey', compact('result', 'survey', 'chapter_id', 'chapter_name'));
        } else {
            // TODO: permission denied page.
            print "Permission denied";
        }
    }

    public function removeQuestions($survey_id) {
        $user = Auth::user();
        $request_parameter = (array) Request::all();
        $slide_to_remove_array = explode("_", $request_parameter['slides_to_remove']);

        //print_r($slide_to_remove_array);

        if ($this->hasPermission($user['id'], $survey_id)) {
            DB::transaction(function() use ($slide_to_remove_array, $survey_id) {
                for ($x = 0; $x < sizeof($slide_to_remove_array); $x++) {
                    DB::table('questions')->where(['survey_id' => $survey_id, 'slide_number' => $slide_to_remove_array[$x]])->delete();
                }
            });
        } else {
            // TODO: Permission denied page
            print "Permission denied";
        }

        return redirect()->route('survey', ['survey_id' => $survey_id]);
    }

    /**
     * Check if a survey already has a question for a certain slide number.
     *
     * @param $survey_id    id of survey we are checking the above condition.
     * @param $slide_number check if this slide number has already a question.
     * @return true if survey has for the given slide number a question, else false.
     **/
    public function slideNumberExists($survey_id, $slide_number) {
        $slide_number_exists =  DB::table('questions')->where('survey_id', $survey_id)->where('slide_number', $slide_number)->get()->count() > 0;
        Debugbar::info($slide_number_exists );
        return response()->json([
            'slideNumberExists' => $slide_number_exists,
        ]);
    }
}