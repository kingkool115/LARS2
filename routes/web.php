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

Route::get('/', function () {
    if(Auth::guest())
    {
        return redirect('/login');
    }
    return redirect('/lectures');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/lectures', 'LectureController@show_lectures');

Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

// verify E-Mail when register new user account
Route::get('verify/{email}/{verifyToken}', 'Auth\RegisterController@sendEmailDone')->name('sendEmailDone');

// verify E-Mail when register new user account
Route::get('/survey/{survey_id}', 'SurveyController@showQuestions')->name('survey');

// check if a slide number for a certain survey already exists.
Route::get('/survey/{survey_id}/slide_number_exists/{slide_number}', 'SurveyController@slideNumberExists')->name('slide_number_exists');

// edit a question
Route::get('/survey/{survey_id}/slide_number/{slide_number}', 'QuestionController@editQuestion')->name('question');


Route::post('upload_image', function(){
    request()->file('question-image')->store('question-images');

    return back();
});