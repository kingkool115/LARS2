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

use App\LectureModel;
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

Route::get('api/switch_slide/lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/question/{question_id}', 'PushControllerController@switchSlide');

Route::get('/lectures', 'MainController@show_lectures')
    ->name('lectures');

Route::get('/overview', 'MainController@show_overview')
    ->name('overview');

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

// rename lecture
Route::get('rename/lecture/{lecture_id}/rename/{new_lecture_name}', 'LectureController@renameLecture')
    ->name('rename_lecture');

// rename chapter
Route::get('rename/lecture/{lecture_id}/chapter/{chapter_id}/rename/{new_chapter_name}', 'ChapterController@renameChapter')
    ->name('rename_chapter');

// rename survey
Route::get('rename/lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/rename/{new_survey_name}', 'SurveyController@renameSurvey')
    ->name('rename_survey');

// show questions of an survey
Route::get('lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}', 'SurveyController@showQuestions')
    ->name('survey');

// remove one or more questions from a survey
Route::get('lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/remove_slides/', 'SurveyController@removeQuestions')
    ->name('remove_questions');

// edit a question
Route::get('lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/question/{question_id}', 'QuestionController@editQuestion')
    ->name('question');

// edit a question
Route::get('lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/create_new_question', 'QuestionController@createNewQuestion')
    ->name('create_new_question');

//get image of question
Route::get('question_image/lecture/{lecture_id}/file/{filename}', 'QuestionController@getImage')
    ->name('question_image');

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
Route::post('lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/create_text_response_question', 'QuestionController@postTextResponseQuestion')
    ->name('postTextResponseQuestion');

// post a multiple choice question
Route::post('lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/create_multiple_choice_question', 'QuestionController@postMultipleChoiceQuestion')
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

// TODO: call an url to start a survey session

// Push a question to device, when this url is called.
Route::get('/api/push/lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/question/{question_id}',
    'PushControllerController@pushQuestion');

Route::get('subscribe', 'CommunicationInterfaceController@subscribe');

Route::get('unsubscribe', 'CommunicationInterfaceController@unsubscribe');

Route::get('start_presentation_session', 'CommunicationInterfaceController@startPresentationSession');

Route::get('push_question', 'CommunicationInterfaceController@pushQuestion');

Route::get('answer_question', 'CommunicationInterfaceController@answerQuestion');

Route::get('evaluate_answers', 'CommunicationInterfaceController@evaluateAnswers');