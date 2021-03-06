<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 23.05.2017
 * Time: 16:15
 */

namespace App\Http\Controllers;

use App\ChapterModel;
use App\QuestionModel;
use App\SurveyModel;
use App\AnswerModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class SurveyController extends Controller
{

    private $lecture_id;
    private $chapter_id;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth.basic');
    }


    /**
     * This function checks if a survey with given survey id exists in DB.
     *
     * @param $survey_id id of the survey to check.
     * @return bool true, if survey exists in DB. Else false.
     */
    private function surveyExists($survey_id){
        return sizeof(DB::table('survey')->select('id', 'name')->where('id', $survey_id)->get()) > 0;
    }

    /**
     * This function is called for GET: lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/
     * It show's all the questions of a certain survey.
     *
     * @param $lecture_id id of the lecture this survey belongs to.
     * @param $chapter_id id of the chapter this survey belongs to.
     * @param $survey_id id of survey that the view shows us.
     * @return a overview of all questions belonging to this survey.
     */
    public function showQuestions($lecture_id, $chapter_id, $survey_id)
    {
        $this->lecture_id = $lecture_id;
        $this->chapter_id = $chapter_id;

        if ($this->hasPermission($lecture_id)) {
            if ($this->surveyExists($survey_id)) {
                $all_questions = QuestionModel::where(['survey_id' => $survey_id])->orderBy('created_at')->get();
                $survey = SurveyModel::where('id', $survey_id)->first();
                $chapter = ChapterModel::where('id', $chapter_id)->first();

                // Accept: application/json
                if (request()->wantsJson()) {
                    return response()->json($all_questions);
                }

                return view('survey', compact('all_questions', 'survey', 'chapter', 'lecture_id', 'chapter_id', 'survey_id'));
            } else {
                // TODO: survey does not exist page.
                print "Sorry, but your requested survey does not exist.";
            }
        } else {
            // TODO: permission denied page.
            print "Permission denied";
        }
    }

    /**
     * This function handles route /rename/lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}rename/{new_survey_name}
     * It renames the lecture.
     *
     * @param $lecture_id the lecture the chapter belongs to.
     * @param $chapter_id the id of the chapter which should be renamed.
     * @param $survey_id id of the survey.
     * @param $new_survey_name the new given survey name.
     * @return redirect to survey overview of that chapter.
     * */
    public function renameSurvey($lecture_id, $chapter_id, $survey_id, $new_survey_name) {
        if ($this->hasPermission($lecture_id)) {
            SurveyModel::where(["id" => $survey_id])->update(["name" => $new_survey_name]);
            return redirect()->route('survey', ['lecture_id' => $lecture_id, "chapter_id" => $chapter_id, "survey_id" => $survey_id]);
        }
    }

    /**
     * This function is called when user clicks Remove-Button in survey-view in order to remove questions.
     * Questions will be removed from DB and will be redirect to same page (survey-view).
     *
     * @param $lecture_id id of the lecture this survey belongs to.
     * @param $chapter_id id of the chapter this survey belongs to.
     * @param $survey_id id of survey that the view shows us.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeQuestions($lecture_id, $chapter_id, $survey_id) {
        $request_parameter = (array) Request::all();
        $questions_to_remove_array = explode("_", $request_parameter['questions_to_remove']);

        //print_r($questions_to_remove_array);

        if ($this->hasPermission($lecture_id)) {
            DB::transaction(function() use ($questions_to_remove_array, $survey_id) {
                for ($x = 0; $x < sizeof($questions_to_remove_array); $x++) {
                    QuestionModel::where(['survey_id' => $survey_id, 'id' => $questions_to_remove_array[$x]])->delete();
                }
            });
        } else {
            // TODO: Permission denied page
            print "Permission denied";
        }

        return redirect()->route('survey', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id]);
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
}