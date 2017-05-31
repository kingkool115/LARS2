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
     * This function routes to /survey/{survey_id}/slide_number/{slide_number}
     * TODO: Wenn der Link direkt eingegeben wird, dann muss überprüft werden, ob es für dieses survey_id nicht schon dieselbe slide_number gibt.
     *
     * @param $survey_id this question belongs to.
     * @param $slide_number of powerpoint presentation this question belongs to.
     * @return view question.blade.php
     */
    public function editQuestion($survey_id, $slide_number)
    {
        // TODO: check if survey belongs to correct professor.
        $survey_name = (array) DB::table('survey')->select('name')->where('id', $survey_id)->get()[0];
        $survey_name = $survey_name['name'];

        //Debugbar::info($all_questions);

        return view('question', compact('survey_name', 'slide_number', 'survey_id'));

    }
}