<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 24.05.2017
 * Time: 19:10
 */

namespace App\Http\Controllers;

use App\AnswerModel;
use App\QuestionModel;
use App\SurveyModel;
use App\FileEntryModel;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;

class QuestionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * This function iks called by route
     * lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/create_new_question.
     *
     * @param $lecture_id   id of lecture the question belongs to.
     * @param $chapter_id   id of chapter the question belongs to.
     * @param $survey_id    id of survey the question belongs to.
     * @return question view to edit new question.
     **/
    public function createNewQuestion($lecture_id, $chapter_id, $survey_id) {
        // check if user has permission for this lecture
        if ($this->hasPermission($lecture_id)) {

            // check if lecture, chapter, survey, slide_number belong to each other.
            if ($this->checkLectureDependencies($lecture_id, $chapter_id, $survey_id)) {
                $survey_name = SurveyModel::where('id', $survey_id)->first();
                $survey_name = $survey_name->name;
                print_r($survey_name);
                return view('question', compact('edit_form',  'survey_name', 'lecture_id', 'chapter_id', 'survey_id'));
            }
            else {
                return "Wrong url constellation. This lecture-chapter-survey-slide_number relation does not exist.";
            }
        }
        // TODO: redirect to permission denied page
        return "Permission denied";
    }

    /** 
     * This function routes to lecture/{lecture_id}/chapter/[chapter_id}/survey/{survey_id}/slide_number/{question_id}
     *
     * @param $lecture_id the id of the lecture this question belongs to.
     * @param $chapter_id the id of the chapter this question belongs to.
     * @param $survey_id this question belongs to.
     * @param $question_id id of the question.
     * @return view question.blade.php
     */
    public function editQuestion($lecture_id, $chapter_id, $survey_id, $question_id)
    {
        $survey = SurveyModel::where(['id' => $survey_id])->first();

        // check if user has permission for this lecture
        if ($this->hasPermission($lecture_id)) {


            // check if lecture, chapter, survey, slide_number belong to each other.
            if ($this->checkLectureDependencies($lecture_id, $chapter_id, $survey_id)) {

                $question = QuestionModel::where(['survey_id' => $survey_id, "id" => $question_id])->first();
                $answers = AnswerModel::where(['question_id' => $question_id])->get();
                return view('question', compact( 'question', 'answers', 'survey', 'lecture_id', 'chapter_id', 'survey_id'));
            } else {
                return "Wrong url constellation. This lecture-chapter-survey-slide_number relation does not exist.";
            }
        }
        // TODO: redirect to permission denied page
        return "Permission denied";
    }

    /**
     * This function will create a new text response question and saves it into DB.
     * If question has an image, than store it in /storage/app/public/question-images/users/{user_id}/{survey_id}/{slide_number}.{extension}
     * After successful creation of the question, the user will be redirected to survey-view of the new created question.
     *
     * @param $lecture_id the id of the lecture this question belongs to.
     * @param $chapter_id the id of the chapter this question belongs to.
     * @param $survey_id this question belongs to.
     * @return redirect to survey-view.
     * */
    public function postTextResponseQuestion($lecture_id, $chapter_id, $survey_id) {
        $post_request = Request::all();

        if ($this->hasPermission($lecture_id)) {

            // create an save question to db
            $question = $this->createQuestion($post_request, $survey_id, true);

            // first delete all answers of that question, if available
            AnswerModel::where(['question_id' => $question->id])->delete();

            // save answer to that question
            $answer = new AnswerModel();
            $answer->answer = $post_request['correct-answer'];
            $answer->is_correct = 1;
            $answer->question_id = $question->id;
            $answer->save();

            // return to survey overview
            return redirect()->route('survey', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id]);
        }
        // TODO: redirect to permission denied page
        return "Permission denied";
    }

    /**
     * This function will create a new Multiple Choice question and saves it into DB.
     * If question has an image, than store it in /storage/app/public/question-images/users/{user_id}/{survey_id}/{slide_number}.{extension}
     * After successful creation of the question, the user will be redirected to survey-view of the new created question.
     *
     * @param $lecture_id the id of the lecture this question belongs to.
     * @param $chapter_id the id of the chapter this question belongs to.
     * @param $survey_id this question belongs to.
     * @return redirect to survey-view.
     * */
    public function postMultipleChoiceQuestion($lecture_id, $chapter_id, $survey_id) {
        $post_request = Request::all();
        //print_r($post_request);
        if ($this->hasPermission($lecture_id)) {

            $question = $this->createQuestion($post_request, $survey_id, false);

            // first delete all answers of that question, if available
            AnswerModel::where(['question_id' => $question->id])->delete();

            // iterate answers from post_request and save them to db.
            // answers = ['possible_answer_1' => 1, 'possible_answer_2' = 0, 'possible_answer_3' => 0]
            foreach ($post_request as $key => $value) {
                if (starts_with($key, 'possible_answer_')) {
                    // which answer
                    $x = explode("possible_answer_", $key)[1];

                    // answer content
                    $answer_content = $post_request['possible_answer_' . $x];

                    // if answers field is empty
                    if (strlen(trim($answer_content)) < 1) {
                        continue;
                    }

                    // check if answer is correct
                    $is_answer_correct = 0;
                    if (isset($post_request['is_answer_correct_' . $x])) {
                        $is_answer_correct = 1;
                    }

                    $answer = new AnswerModel();
                    $answer->question_id = $question->id;
                    $answer->answer = $answer_content;
                    $answer->is_correct = $is_answer_correct;
                    $answer->save();
                }
            }

            // return to survey overview
            return redirect()->route('survey', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id]);
        }
        // TODO: redirect to permission denied page
        return "Permission denied";
    }

    /**
     * Return saved image to current question.
     *
     * @param $lecture_id to check permission.
     * @param $filename in local storage.
     * @return image.
     * */
    public function getImage($lecture_id, $filename) {
        if ($this->hasPermission($lecture_id)) {
            $entry = FileEntryModel::where('filename', '=', $filename)->firstOrFail();
            $file = Storage::disk('local')->get($entry->filename);

            return (new Response($file, 200))
                ->header('Content-Type', $entry->mime);
        } else {
            // TODO: redirect to permission denied page
            return "Permission denied";
        }
    }

    /**
     * Create a question into questions-database when clicking on submit button in question-view.
     *
     * @param $post_request contains all necessary information about the posted question.
     * @param $survey_id is the id of survey the question belongs to.
     * @param $is_text_response true if it's a text-response-question, else false.
     * @return QuestionModel instance of the save question.
     **/
    private function createQuestion($post_request, $survey_id, $is_text_response) {

        if (isset($post_request['question_id'])) {
            // just update existing question
            $question = QuestionModel::find($post_request['question_id']);
        } else {
            // insert a new question to questions table.
            $question = new QuestionModel();
        }

        $question->question  = $post_request['question'];
        $question->survey_id = $survey_id;
        $question->is_text_response = $is_text_response;
        $question->save();

        // if an image is uploaded
        if ($is_text_response) {
            $file = request()->file('question-image-text-response');
        } else {
            $file = request()->file('question-image-multiple-choice');
        }


        if ($file != null) {
            // add file to app/question-images/
            $ext = $file->guessClientExtension();
            Storage::disk('local')->put($file->getFilename().'.'.$ext,  File::get($file));

            // create an entry into DB
            $entry = new FileEntryModel();
            $entry->mime = $file->getClientMimeType();
            $entry->original_filename = $file->getClientOriginalName();
            $entry->filename = $file->getFilename().'.'.$ext;
            $entry->save();

            $question->image_path = $entry->filename;
        } else {
            $question->image_path = null;
        }
        $question->save();

        return $question;
    }
}