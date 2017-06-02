<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 24.05.2017
 * Time: 19:10
 */

namespace App\Http\Controllers;

use Barryvdh\Debugbar\Facade as Debugbar;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
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


    public function hasPermission($user_id, $survey_id) {
        return sizeof(DB::table('survey')->where(['user_id' => $user_id, 'id' => $survey_id])->get()) > 0;
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
        $user = Auth::user();
        $survey_name = (array)DB::table('survey')->select('name')->where('id', $survey_id)->get()[0];
        $survey_name = $survey_name['name'];

        // TODO: check if we create a new question with empty fields or just edit an already existing question.
        if ($this->hasPermission($user['id'], $survey_id)) {
            $question = DB::table('questions')->where(['survey_id' => $survey_id, "slide_number" => $slide_number])->get();
            //print_r(explode('-', $question['correct_answers']));
            // if we want to just edit an existing question
            if (sizeof($question) > 0) {
                $question = (array)$question[0];
                print_r(explode('-', $question['correct_answers']));
                // print_r($question);
                $edit_form = 1;
                return view('question', compact('edit_form', 'question', 'survey_name', 'slide_number', 'survey_id'));
            // if we want to create a completely new question
            } else {
                $edit_form = 0;
                return view('question', compact('edit_form','question', 'survey_name', 'slide_number', 'survey_id'));
            }
        }else {
            // TODO: redirect to permission denied page
            return "Permission denied";
        }
    }

    public function postTextResponseQuestion() {
        $user = Auth::user();
        $post_request = Request::all();
        $survey_id = $post_request['survey_id'];

        if ($this->hasPermission($user['id'], $survey_id)) {
            $slide_number = $post_request['slide_number'];

            // if an image is uploaded
            $file = request()->file('question-image-text-response');
            if ($file != null) {
                $ext = $file->guessClientExtension();
                // users/{user_id}/{survey_id}/{slide_number}/
                // TODO: store them somewhere else
                // this file path will be saved into DB
                $path = 'question-images/users/' . $user['id'] . '/' . $survey_id .'/';
                // actually the file is stored in public/question-images ...
                $file->storeAs('public/' . $path,  $slide_number . "." . $ext);
            }

            // finally create dictionary with all necessary entries for our DB.
            // The key of this dictionary should be the same as the column names of table 'questions'.
            $question_db_entry = array();
            $question_db_entry['survey_id'] = $survey_id;
            $question_db_entry['slide_number'] = $slide_number;
            $question_db_entry['question'] = $post_request['question'];
            $question_db_entry['correct_text_response'] = $post_request['correct-answer'];
            // users/{user_id}/{survey_id}/{slide_number}/
            if ($file != null) {
                $question_db_entry['image_path'] = $path . '/' . $slide_number . '.' . $ext;
            }
            $question_db_entry['is_text_response'] = 1;
            $question_db_entry['show_result_on_next_slide'] = $post_request['when-to-show-results'] == 'next-slide';

            // first try to delete row with this slide_number and survey_id
            DB::table('questions')->where(['slide_number' => (string) $slide_number, 'survey_id' => (string)$survey_id])->delete();
            // insert new row to table 'questions'
            DB::table('questions')->insert($question_db_entry);

            // return to survey overview
            return redirect()->route('survey', ['survey_id' => $survey_id]);
        } else {
            // TODO: redirect to permission denied page
            print "Permission denied";
        }
    }

    public function postMultipleChoiceQuestion() {
        //request()->file('question_for_slide_number-image')->store('question_for_slide_number-images/users/');
        $user = Auth::user();
        $post_request = Request::all();
        $survey_id = $post_request['survey_id'];

        if ($this->hasPermission($user['id'], $survey_id)) {
            $slide_number = $post_request['slide_number'];

            // if an image is uploaded
            $file = request()->file('question-image-multiple-choice');
            if ($file != null) {
                $ext = $file->guessClientExtension();
                // users/{user_id}/{survey_id}/{slide_number}/
                // TODO: store them somewhere else
                // this file path will be saved into DB
                $path = 'question-images/users/' . $user['id'] . '/' . $survey_id .'/';
                // actually the file is stored in public/question-images ...
                $file->storeAs('public/' . $path,  $slide_number . "." . $ext);
            }

            // create a dictionary of answers and if they are true or false
            // answers = ['answer_1' => 1, 'answer_2' = 0, 'answer_3' => 0]
            $answers = array();
            foreach ($post_request as $key => $value) {
                if (starts_with($key, 'possible_answer_')) {
                    // which answer
                    $x = explode("possible_answer_", $key)[1];
                    // answer content
                    $possible_answer = $post_request['possible_answer_' . $x];
                    if (strlen(trim($possible_answer)) < 1) {
                        continue;
                    }
                    $answers[$possible_answer] = 0;
                    if (isset($post_request['is_answer_correct_' . $x])) {
                        $answers[$possible_answer] = 1;
                    }
                }
            }

            // finally create dictionary with all necessary entries for our DB.
            // The key of this dictionary should be the same as the column names of table 'questions'.
            $question_db_entry = array();
            $question_db_entry['survey_id'] = $survey_id;
            $question_db_entry['slide_number'] = $slide_number;
            $question_db_entry['question'] = $post_request['question'];

            if ($file != null) {
                $question_db_entry['image_path'] = $path . '/' . $slide_number . '.' . $ext;
            }

            $answers_counter = 1;
            $correct_answers = "";

            // used to see if this question is a multiple choice multiple selection question. yes if > 1.
            $correct_answers_counter = 0;

            // collect possible answers from form.
            foreach ($answers as $answer => $is_correct) {
                $question_db_entry['answer_' . $answers_counter] = $answer;
                $correct_answers = $correct_answers . $is_correct . "-";
                if ($is_correct == 1) {
                    $correct_answers_counter += 1;
                }
                $answers_counter += 1;
            }

            $question_db_entry['correct_answers'] = $correct_answers;
            $question_db_entry['is_multi_select'] = $correct_answers_counter > 1;
            $question_db_entry['is_text_response'] = 0;
            $question_db_entry['show_result_on_next_slide'] = $post_request['when-to-show-results'] == 'next-slide';

            // first try to delete row with this slide_number and survey_id
            DB::table('questions')->where(['slide_number' => (string) $slide_number, 'survey_id' => (string)$survey_id])->delete();
            // insert new row to table 'questions'
            DB::table('questions')->insert($question_db_entry);

            // print_r($question_db_entry);
            // return to survey overview
            return redirect()->route('survey', ['survey_id' => $survey_id]);
        } else {
            // TODO: redirect to permission denied page
            print "Permission denied";
        }

    }
}