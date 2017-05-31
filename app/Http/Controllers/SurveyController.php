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

class SurveyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('web');
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
        // TODO: check if survey belongs to correct professor.
        $all_questions = DB::table('questions')->select('id', 'survey_id', 'question', 'slide_number')->where('survey_id', $survey_id)->orderBy('slide_number')->get();
        $survey_name = (array) DB::table('survey')->select('name')->where('id', $survey_id)->get()[0];

        $result = [];
        foreach ($all_questions as $question) {
            $result[] = (array) $question;
        }

        Debugbar::warning($survey_name['name']);
        return view('survey', compact('result', 'survey_name'));
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