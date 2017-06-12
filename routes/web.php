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
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    if(Auth::guest())
    {
        return redirect('/login');
    }
    return redirect('/lectures');
});

Auth::routes();

Route::get('/home', 'HomeController@index')
    ->name('home');

Route::get('/lectures', 'MainController@show_lectures')
    ->name('lectures');

Route::post('/logout', 'Auth\LoginController@logout')
    ->name('logout');

// verify E-Mail when register new user account
Route::get('verify/{email}/{verifyToken}', 'Auth\RegisterController@sendEmailDone')
    ->name('sendEmailDone');

// remove one or more lectures
Route::get('lecture/remove_lectures/', 'MainController@removeLectures')
    ->name('remove_lectures');

// create a new lecture
Route::get('lecture/{lecture_name}', 'MainController@createNewLecture')
    ->name('create_lecture');

// show questions of an survey
Route::get('lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}', 'SurveyController@showQuestions')
    ->name('survey');

// check if a slide number for a certain survey already exists.
Route::get('lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/slide_number_exists/{slide_number}', 'SurveyController@slideNumberExists')
    ->name('slide_number_exists');

// remove one or more questions from a survey
Route::get('lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/remove_slides/', 'SurveyController@removeQuestions')
    ->name('remove_questions');

// edit a question
Route::get('lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/slide_number/{slide_number}', 'QuestionController@editQuestion')
    ->name('question');

// show all chapters of a lecture
Route::get('lecture/{lecture_id}/chapters/', 'LectureController@showChapters')
    ->name('lecture');

// remove one or more chapters from a lecture
Route::get('lecture/{lecture_id}/remove_chapters/', 'LectureController@removeChapters')
    ->name('remove_chapters');

// create a new chapter
Route::get('lecture/{lecture_id}/chapter/{chapter_name}', 'LectureController@createNewChapter')
    ->name('create_chapter');

// show all surveys of a chapter
Route::get('lecture/{lecture_id}/chapter/{chapter_id}/surveys/', 'ChapterController@showSurveys')
    ->name('chapter');

// remove one or more questions from a survey
Route::get('lecture/{lecture_id}/chapter/{chapter_id}/remove_surveys/', 'ChapterController@removeSurveys')
    ->name('remove_surveys');

// create a new survey
Route::get('lecture/{lecture_id}/chapter/{chapter_id}/survey_name/{survey_name}', 'ChapterController@createNewSurvey')
    ->name('create_survey');

// post a text response question
Route::post('lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/slide_number/{slide_number}/create_text_response_question', 'QuestionController@postTextResponseQuestion')
    ->name('postTextResponseQuestion');

// post a multiple choice question
Route::post('lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/slide_number/{slide_number}/create_multiple_choice_question', 'QuestionController@postMultipleChoiceQuestion')
    ->name('postMultipleChoiceQuestion');

// show create survey form
Route::get('create_new_survey', 'CreateNewSurveyController@showCreateSurveyForm')
    ->name('show_create_survey_form');

// post create new survey for new lecture form
Route::post('create_new_survey/new_lecture', 'CreateNewSurveyController@postNewSurveyForNewLecture')
    ->name('post_new_survey_for_new_lecture');

// post create new survey for existing lecture form
Route::post('create_new_survey/existing_lecture/{lecture_id}', 'CreateNewSurveyController@postNewSurveyForExistingLecture')
    ->name('post_new_survey_for_existing_lecture');

// post create new survey for existing chapter form
Route::post('create_new_survey/existing_chapter/{lecture_id}/{chapter_id}', 'CreateNewSurveyController@postNewSurveyForExistingChapter')
    ->name('post_new_survey_for_existing_chapter');


// Get all lectures of user
Route::get('/api/lectures', ['middleware' => 'auth.basic', function() {
    $user = Auth::user();
    $lectures = DB::table('lecture')->select('id', 'name')->where(['user_id' => $user['id']])->get();
    return response()->json(['lectures' => $lectures]);
}]);


// Get all Chapters of a certain lecture
Route::get('/api/lectures/{lecture_id?}/chapters', ['middleware' => 'auth.basic', function($lecture_id) {
    $user = Auth::user();
    $chapters = DB::table('chapter')->select('id', 'name')->where(['lecture_id' => $lecture_id])->get();
    $has_permission = DB::table('lecture')->where(['user_id' => $user['id'], 'id' => $lecture_id])->get()->count() > 0;
    if ($has_permission) {
        return response()->json(['chapters' => $chapters]);
    } else {
        return response('Permission denied', 403);
    }
}]);

// Get all Chapters of a certain lecture
Route::get('/api/lectures/{lecture_id?}/chapters/{chapter_id?}/surveys', ['middleware' => 'auth.basic', function($lecture_id, $chapter_id) {
    $user = Auth::user();
    $has_permission = DB::table('lecture')->where(['user_id' => $user['id'], 'id' => $lecture_id])->get()->count() > 0;
    $chapters_result  = DB::table('chapter')->select('id')->where(['lecture_id' => $lecture_id])->get();

    if ($has_permission) {
        foreach ($chapters_result as $chapter) {
            if ($chapter->id == $chapter_id) {
                $surveys = DB::table('survey')->select('id', 'name')->where(['chapter_id' => $chapter_id])->get();
                return response()->json(['surveys' => $surveys]);
            }
        }
        return response("No surveys found for given parameters", 404);
    } else {
        return response('Permission denied', 403);
    }
}]);