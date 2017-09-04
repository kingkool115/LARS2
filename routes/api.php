<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('all_lectures', 'CommunicationInterfaceController@getAllAvailableLectures');

Route::get('lecture/{lecture_id}/all_chapters', 'CommunicationInterfaceController@getChaptersOfLectureNoAuth');

Route::post('subscribe', 'CommunicationInterfaceController@subscribe');

Route::post('unsubscribe', 'CommunicationInterfaceController@unsubscribe');

Route::get('start_presentation_session', 'CommunicationInterfaceController@startPresentationSession');

Route::get('push_question', 'CommunicationInterfaceController@pushQuestion');

Route::post('answer_question', 'CommunicationInterfaceController@answerQuestion');

Route::get('evaluate_answers', 'CommunicationInterfaceController@evaluateAnswers');

Route::get('get_answers_of_one_question/{question_id}/{session_id}', 'CommunicationInterfaceController@getAnswersOfOneQuestion');