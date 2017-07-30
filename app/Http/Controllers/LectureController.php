<?php
/**
 * Created by PhpStorm.
 * User: george
 * Date: 05.06.17
 * Time: 16:10
 */

namespace App\Http\Controllers;

use App\AnswerModel;
use App\ChapterModel;
use App\LectureModel;
use App\QuestionModel;
use App\SurveyModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class LectureController extends Controller
{

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth.basic');
    }


    /**
     * This function checks if a lecture with given survey id exists in DB.
     *
     * @param $lecture_id id of the lecture to check.
     * @return bool true, if survey exists in DB. Else false.
     */
    private function lectureExists($lecture_id){
        return LectureModel::where('id', $lecture_id)->get()->count() > 0;
    }

    /**
     * This function handles route lecture/{lecture_id}/chapters.
     * It gives an overview over all chapters that belong to a lecture.
     * Also called by PowerPoint over RestAPI.
     *
     * @param $lecture_id is the id of the lecture of this view.
     * @return lecture.blade.php
     */
    public function showChapters($lecture_id) {
        $all_chapters = ChapterModel::where(['lecture_id' => $lecture_id])->get();
        $lecture = LectureModel::where(['id' => $lecture_id])->first();

        if ($this->hasPermission($lecture_id)) {
            if ($this->lectureExists($lecture_id)) {

                // Accept: application/json
                if (request()->wantsJson()) {
                    return response()->json($all_chapters);
                }

                // Accept: text/html
                return view('lecture', compact('all_chapters', 'lecture', 'lecture_id'));
            }
            // TODO: chapter does not exist page.
            print "Sorry, but your requested chapter does not exist.";
        } else {
            // TODO: Permission denied page.
            print "Permission denied.";
        }
    }

    /**
     * This function creates a new chapter entry into DB.
     *
     * @param $lecture_id id of this lecture.
     * @param $chapter_name chapter title of the chapter the user wants to create.
     * @return redirect to new created chapter view.
     */
    public function createNewChapter($lecture_id, $chapter_name) {
        if ($this->hasPermission($lecture_id)) {
            $new_chapter = new ChapterModel();
            $new_chapter->lecture_id = $lecture_id;
            $new_chapter->name = $chapter_name;
            $new_chapter->save();
            return redirect()->route('chapter', ['lecture_id' => $lecture_id, 'chapter_id' => $new_chapter->id]);
        } else {
            // TODO: Permission denied page.
            print "Permission denied";
        }
    }

    /**
     * This function is called when user clicks Remove-Button in lecture-view in order to remove chapters of that lecture.
     * Chapters will be removed from DB and will be redirected to same page (lecture-view).
     *
     * @param $lecture_id id of this lecture.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeChapters($lecture_id) {
        $request_parameter = (array) Request::all();
        $chapter_to_remove_array = explode("_", $request_parameter['chapters_to_remove']);

        //print_r($chapter_to_remove_array);

        if ($this->hasPermission($lecture_id)) {
            DB::transaction(function() use ($chapter_to_remove_array) {

                // delete chapter entry.
                // delete all survey entries which belong to that chapter.
                for ($x = 0; $x < sizeof($chapter_to_remove_array); $x++) {
                    ChapterModel::where(['id' => $chapter_to_remove_array[$x]])->delete();
                    SurveyModel::where(['chapter_id' => $chapter_to_remove_array[$x]])->delete();
                }

                // delete all questions which have a survey_id which is no longer available in table survey.
                $all_questions = QuestionModel::all();
                foreach ($all_questions as $question) {
                    $survey_exists =  SurveyModel::where(['id' => $question->survey_id])->get()->count() > 0;
                    if (!$survey_exists) {
                        QuestionModel::where(['survey_id' => $question->survey_id])->delete();
                        AnswerModel::where(['question_id' => $question->id])->delete();
                    }
                }
            });
        } else {
            // TODO: Permission denied page
            print "Permission denied";
        }
        return redirect()->route('lecture', ['lecture_id' => $lecture_id]);
    }
}