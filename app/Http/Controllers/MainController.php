<?php

namespace App\Http\Controllers;

use App\QuestionModel;
use App\util\Chapter;
use App\util\Lecture;
use App\ChapterModel;
use App\LectureModel;
use App\util\Survey;
use App\SurveyModel;
use Illuminate\Support\Facades\DB;
use Barryvdh\Debugbar\Facade as Debugbar;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Created by PhpStorm.
 * User: User
 * Date: 15.05.2017
 * Time: 20:36
 */

class MainController extends Controller
{

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * This function checks if a user is logged in.
     *
     * @return bool true if user is authenticated, false if not.
     */
    private function isAuthenticated() {
        $user = Auth::user();
        return isset($user);
    }

    /**
     * Is needed for quick overview and for lectures table.
     * Also called by PowerPoint over RestAPI.
     *
     * @return Response
     */
    public function show_lectures()
    {
        $user = Auth::user();

        $all_lectures = LectureModel::where(['user_id' => $user['id']])->get();
        $all_chapters = ChapterModel::all();
        $all_surveys = SurveyModel::all();

        // a list with objects of type lecture.
        $result = [];

        // iterate through all surveys of DB
        foreach ($all_surveys as $survey) {
            // iterate through all chapters of DB
            foreach ($all_chapters as $chapter) {

                // if survey belongs to chapter
                if ($survey->chapter_id == $chapter->id) {

                    // iterate through all lectures of DB
                    foreach ($all_lectures as $lecture) {

                        // if chapter belongs to lecture of DB.
                        if ($chapter->lecture_id == $lecture->id) {

                            // iterate through $result
                            foreach ($result as $lecture_of_results) {

                                // if lecture already exists in our result list, then just
                                if ($lecture_of_results->getId() == $lecture->id) {

                                    // if chapter already exists -> only a new survey has to be added to the chapter.
                                    if ($lecture_of_results->check_if_chapter_exists($chapter->id)) {
                                        $result_survey = new Survey($survey->id, $survey->name, $chapter->id, null);
                                        $lecture_of_results->getChapterById($chapter->id)->addSurvey($result_survey);
                                        break 2;    // break 2 loops -> continue with nex survey of $all_surveys

                                    // if chapter does not exist in this lecture -> create new chapter with survey and
                                    // add it to existing lecture in result list.
                                    } else {
                                        $result_survey = new Survey($survey->id, $survey->name, $chapter->id, null);
                                        $result_chapter = new Chapter($chapter->id, $chapter->name, $result_survey);
                                        $lecture_of_results->addChapter($result_chapter);
                                        break 2;    // break 2 loops -> continue with nex survey of $all_surveys
                                    }
                                }
                            }

                            // if lecture does not exists in our result list -> create a new lecture and add it to result list.
                            $result_survey = new Survey($survey->id, $survey->name, $chapter->id, null);
                            $result_chapter = new Chapter($chapter->id, $chapter->name, $result_survey);
                            $result_lecture = new Lecture($lecture->id, $lecture->name, $result_chapter);
                            $result[] = $result_lecture;
                            break;
                        }
                    }
                }
            }
        }

        if (request()->wantsJson()) {
            return response()->json(LectureModel::all());
        }
        return view('main', ['lectures' => $result]);
    }

    /**
     * This function creates a new chapter entry into DB.
     *
     * @param $lecture_name Lecture title of the chapter the user wants to create.
     * @return redirect to new created chapter view.
     */
    public function createNewLecture($lecture_name) {
        $user = Auth::user();
        if ($this->isAuthenticated()) {
            $new_lecture = new LectureModel();
            $new_lecture->name = $lecture_name;
            $new_lecture->user_id = $user['id'];
            $new_lecture->save();
            return redirect()->route('lecture', ['lecture_id' => $new_lecture->id]);
        } else {
            // TODO: Permission denied page.
            print "Permission denied";
        }
    }

    /**
     * This function is called when user clicks Remove-Button in main-view in order to remove lectures.
     * Chapters will be removed from DB and will be redirected to same page (main-view).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeLectures() {
        $request_parameter = (array) Request::all();
        $lecture_to_remove_array = explode("_", $request_parameter['lectures_to_remove']);

        //print_r($lecture_to_remove_array);

        if ($this->isAuthenticated()) {
            DB::transaction(function() use ($lecture_to_remove_array) {

                // delete lecture entry.
                // delete all chapter entries which belong to that lecture.
                for ($x = 0; $x < sizeof($lecture_to_remove_array); $x++) {
                    LectureModel::where(['id' => $lecture_to_remove_array[$x]])->delete();
                    ChapterModel::where(['lecture_id' => $lecture_to_remove_array[$x]])->delete();
                }

                // delete all surveys which have a chapter_id which is no longer available in table chapter.
                $all_surveys = SurveyModel::all();
                foreach ($all_surveys as $survey) {
                    $chapter =  ChapterModel::where(['id' => $survey->chapter_id])->first();
                    if (!isset($chapter)) {
                        SurveyModel::where(['chapter_id' => $survey->chapter_id])->delete();
                    }
                }

                // delete all questions which have a survey_id which is no longer available in table survey.
                $all_questions = QuestionModel::all();
                foreach ($all_questions as $question) {
                    $survey =  SurveyModel::where(['id' => $question->survey_id])->first();
                    if (!isset($survey)) {
                        QuestionModel::where(['survey_id' => $question->survey_id])->delete();
                    }
                }
            });
        } else {
            // TODO: Permission denied page
            print "Permission denied";
        }

        return redirect()->route('lectures');
    }
}