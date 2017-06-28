<?php
/**
 * Created by PhpStorm.
 * User: gest3747
 * Date: 07.06.2017
 * Time: 07:23
 */

namespace App\Http\Controllers;

use App\ChapterModel;
use App\LectureModel;
use App\SurveyModel;
use App\util\Chapter;
use App\util\Survey;
use App\util\Lecture;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;


class CreateNewSurveyController extends Controller
{

    /**
     * Create a new controller instance.
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
    private function isAuthenticated()
    {
        $user = Auth::user();
        return isset($user);
    }

    /**
     * This function displays the form when you click on 'Create new survey Button'.
     * */
    function showCreateSurveyForm()
    {
        if ($this->isAuthenticated()) {
            //$surveys = DB::select($this->select_all_surveys_of_prof);
            //
            $all_lectures = LectureModel::all();
            $all_chapters = ChapterModel::all();
            $all_surveys = SurveyModel::all();

            // a list with objects of type lecture.
            $result = [];
            $result_chapters = [];

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

                                        // if lecture already exists in our result list, then just
                                        if ($lecture_of_results->getId() == $lecture->id) {

                                            // if chapter already exists -> only a new survey has to be added to the chapter.
                                            if ($lecture_of_results->check_if_chapter_exists($chapter->id)) {
                                                $result_surveys = new Survey($survey->id, $survey->name, $chapter->id, null);
                                                $lecture_of_results->getChapterById($chapter->id)->addSurvey($result_surveys);
                                                break 2;    // break 2 loops -> continue with nex survey of $all_surveys

                                                // if chapter does not exist in this lecture -> create new chapter with survey and
                                                // add it to existing lecture in result list.
                                            } else {
                                                $result_surveys = new Survey($survey->id, $survey->name, $chapter->id, null);
                                                $result_chapters = new Chapter($chapter->id, $chapter->name, $result_surveys);
                                                $lecture_of_results->addChapter($result_chapters);
                                                break 2;    // break 2 loops -> continue with nex survey of $all_surveys
                                            }
                                        }
                                    }
                                }

                                // if lecture does not exists in our result list -> create a new lecture and add it to result list.
                                $result_surveys = new Survey($survey->id, $survey->name, $chapter->id, null);
                                $result_chapters = new Chapter($chapter->id, $chapter->name, $result_surveys);
                                $result_lectures = new Lecture($lecture->id, $lecture->name, $result_chapters);
                                $result[] = $result_lectures;
                                break;
                            }
                        }
                    }
                }
            }
            return view('create_new_survey',  ['lectures' => $result, 'chapters' => (array)$result_chapters]);
        }
    }

    /**
     * Is called when a complete new lecture with new chapter and new survey is created.
     * Insert name values in lecture, chapter, survey table and redirect to questions-overview of new created survey.
     */
    function postNewSurveyForNewLecture() {
        $user = Auth::user();
        $post_request = Request::all();

        $lecture_name = $post_request['lecture-new-lecture'];
        $chapter_name = $post_request['chapter-new-lecture'];
        $survey_name = $post_request['survey-new-lecture'];

        $lecture_id = 0;
        $chapter_id = 0;
        $survey_id = 0;

        DB::transaction(function() use ($user, $lecture_name, &$lecture_id, $chapter_name, &$chapter_id, $survey_name, &$survey_id) {
            $new_lecture = new LectureModel();
            $new_lecture->name = $lecture_name;
            $new_lecture->user_id = $user['id'];
            $new_lecture->save();

            $new_chapter = new ChapterModel();
            $new_chapter->name = $chapter_name;
            $new_chapter->lecture_id = $new_lecture->id;
            $new_chapter->save();

            $new_survey = new SurveyModel();
            $new_survey->name = $survey_name;
            $new_survey->chapter_id = $new_chapter->id;
            $new_survey->save();

            $lecture_id = $new_lecture->id;
            $chapter_id = $new_chapter->id;
            $survey_id = $new_survey->id;
        });

        return redirect()->route('survey', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id]);
    }

    /**
     * Is called when a new chapter with new survey should be created for a certain lecture.
     * Insert created chapter and survey names into DB and redirect to questions-overview of new created survey.
     *
     * @param $lecture_id id of lecture the new chapter and survey belongs to.
     * @return redirect to questions-overview of new created survey
     */
    function postNewSurveyForExistingLecture($lecture_id) {
        $post_request = Request::all();

        $chapter_name = $post_request['chapter-new-chapter'];
        $survey_name = $post_request['survey-new-chapter'];

        $chapter_id = 0;
        $survey_id = 0;

        DB::transaction(function() use ($lecture_id, $chapter_name, &$chapter_id, $survey_name, &$survey_id) {
            $new_chapter = new ChapterModel();
            $new_chapter->name = $chapter_name;
            $new_chapter->lecture_id = $lecture_id;
            $new_chapter->save();

            $new_survey = new SurveyModel();
            $new_survey->name = $survey_name;
            $new_survey->chapter_id = $new_chapter->id;
            $new_survey->save();

            $chapter_id = $new_chapter->id;
            $survey_id = $new_survey->id;
        });

        return redirect()->route('survey', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id]);
    }

    /**
     * Is called when a new survey should be created for a certain lecture and chapter.
     * Insert created survey name into DB and redirect to questions-overview of new created survey.
     *
     * @param $lecture_id id of lecture the new survey belongs to.
     * @param $chapter_id id of chapter the new survey belongs to.
     * @return redirect to questions-overview of new created survey
     */
    function postNewSurveyForExistingChapter($lecture_id, $chapter_id) {
        $post_request = Request::all();

        $survey_name = $post_request['survey-new-survey'];

        $survey_id = 0;

        DB::transaction(function() use ($lecture_id, $chapter_id, $survey_name, &$survey_id) {
            $new_survey = new SurveyModel();
            $new_survey->name = $survey_name;
            $new_survey->chapter_id = $chapter_id;
            $new_survey->save();

            $survey_id = $new_survey->id;
        });

        return redirect()->route('survey', ['lecture_id' => $lecture_id, 'chapter_id' => $chapter_id, 'survey_id' => $survey_id]);
    }
}