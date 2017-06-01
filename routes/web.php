<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use \Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facade as Debugbar;
use Illuminate\Support\Facades\Input;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Support\Facades\Request;

Route::get('/', function () {
    if(Auth::guest())
    {
        return redirect('/login');
    }
    return redirect('/lectures');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/lectures', 'LectureController@show_lectures')->name('lectures');

Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

// verify E-Mail when register new user account
Route::get('verify/{email}/{verifyToken}', 'Auth\RegisterController@sendEmailDone')->name('sendEmailDone');

// verify E-Mail when register new user account
Route::get('/survey/{survey_id}', 'SurveyController@showQuestions')->name('survey');

// check if a slide number for a certain survey already exists.
Route::get('/survey/{survey_id}/slide_number_exists/{slide_number}', 'SurveyController@slideNumberExists')->name('slide_number_exists');

// edit a question
Route::get('/survey/{survey_id}/slide_number/{slide_number}', 'QuestionController@editQuestion')->name('question');


Route::post('create_text_response_question', function(){
    //request()->file('question-image')->store('question-images/users/');
    $user = Auth::user();
    $survey_id = request()->get('survey_id');
    $slide_number = request()->get('slide_number');

    $file = request()->file('question-image');
    // if an image is uploaded
    if ($file != null) {
        $ext = $file->guessClientExtension();
        $path = 'question-images/users/' . $user['id'] . "_" . $user['email'] . '/' . 'survey_' . $survey_id;
        $file->storeAs($path, '/slide_number_' . $slide_number . $ext);
    }

    $question = Request::all()['question'];
    $correct_answer =  Input::get('correct-answer');

    $show_result_on_next_slide = false;
    if (Request::all()['when-to-show-results'] == 'next-slide') {
        $show_result_on_next_slide = true;
    }
});


Route::post('create_multiple_choice_question', function(ServerRequestInterface $request){
    //request()->file('question-image')->store('question-images/users/');
    $user = Auth::user();
    $survey_id = request()->get('survey_id');
    $slide_number = request()->get('slide_number');
    $question = Request::all()['question'];

    $file = request()->file('question-image');
    // if an image is uploaded
    if ($file != null) {
        $ext = $file->guessClientExtension();
        // survey_path
        $path = 'question-images/users/' . $user['id'] . "_" . $user['email'] . '/' . 'survey_' . $survey_id;
        $file->storeAs($path, '/slide_number_' . $slide_number . $ext);
    }

    $answers = array();
    foreach (Request::all() as $key => $value) {
        if (starts_with($key, 'possible_answer_')) {
            // which answer
            $x = explode("possible_answer_", $key)[1];
            // answer content
            $possible_answer = Request::all()['possible_answer_' . $x];
            if (strlen(trim($possible_answer)) < 1) {
                continue;
            }
            $answers[$possible_answer] = 0;
            if (isset(Request::all()['is_answer_correct_' . $x])) {
                $answers[$possible_answer] = 1;
            }
        }
    }

    $show_result_on_next_slide = 0;
    if (Request::all()['when-to-show-results'] == 'next-slide') {
        $show_result_on_next_slide = 1;
    }

    $question_db_entry = array();
    $question_db_entry['survey_id'] = $survey_id;
    $question_db_entry['slide_number'] = $slide_number;
    $question_db_entry['question'] = $question;
    if ($file != null) {
        $question_db_entry['image_path'] = $path . '/slide_number_' . $slide_number . $ext;
    }
    $answers_counter = 1;
    $correct_answers = "";

    $correct_answers_counter = 0;
    $is_multi_select = 0;
    foreach ($answers as $answer => $is_correct) {
        $question_db_entry['answer_' . $answers_counter] = $answer;
        $correct_answers = $correct_answers . $is_correct . "-";
        if ($is_correct == 1) {
            $correct_answers_counter += 1;
        }
        $answers_counter += 1;
    }

    if ($correct_answers_counter > 1) {
        $is_multi_select = 1;
    }

    $question_db_entry['correct_answers'] = $correct_answers;
    $question_db_entry['is_multi_select'] = $is_multi_select;
    $question_db_entry['is_text_response'] = 0;
    $question_db_entry['show_result_on_next_slide'] = $show_result_on_next_slide;
    // TODO: check if slide already exists .. if it already exists then do sql update instead of insert
    DB::table('questions')->insert($question_db_entry);
    return redirect()->route('survey', ['survey_id' => $survey_id]);
    // print_r($question_db_entry);
});