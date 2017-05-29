<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 24.05.2017
 * Time: 19:10
 */

namespace App\Http\Controllers;

use Barryvdh\Debugbar\Facade as Debugbar;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
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

    /** 
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function editQuestion($survey_id, $slide_number)
    {
        // TODO: check if survey belongs to correct professor.
        $all_questions = DB::table('questions')->select('id', 'question', 'slide_number')->where('survey_id', $survey_id)->orderBy('slide_number')->get();
        //$foo = (array)$all_questions;
        //Debugbar::info($all_questions);

        $result = [];
        foreach ($all_questions as $question) {
            $result[] = (array)$question;
        }


        Debugbar::info($result);
        return view('question', ['result' => $result]);

    }
}