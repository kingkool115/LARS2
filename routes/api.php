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


Route::get('subscribe', 'CommunicationInterfaceController@subscribe');

Route::get('unsubscribe', 'CommunicationInterfaceController@unsubscribe');

Route::get('start_presentation_session', 'CommunicationInterfaceController@startPresentationSession');

Route::get('push_question', 'CommunicationInterfaceController@pushQuestion');

Route::post('answer_question', 'CommunicationInterfaceController@answerQuestion');

Route::get('evaluate_answers', 'CommunicationInterfaceController@evaluateAnswers');