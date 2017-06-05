<?php
/**
 * Created by PhpStorm.
 * User: george
 * Date: 05.06.17
 * Time: 16:10
 */

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Barryvdh\Debugbar\Facade as Debugbar;

class LectureController extends Controller
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
     * This function checks if a user is permitted to use any functions of this class.
     *
     * @param $lecture_id does the user have the rights for this lecture_id?
     * @return bool true if user has permissions, false if not.
     */
    private function hasPermission($lecture_id) {
        $user = Auth::user();
        return sizeof(DB::table('lecture')->where(['user_id' => $user['id'], 'id' => $lecture_id])->get()) > 0;
    }

    /**
     * This function checks if a lecture with given survey id exists in DB.
     *
     * @param $lecture_id id of the lecture to check.
     * @return bool true, if survey exists in DB. Else false.
     */
    private function lectureExists($lecture_id){
        return sizeof(DB::table('lecture')->where('id', $lecture_id)->get()) > 0;
    }

    /**
     * This function handles route lecture/{lecture_id}/chapters.
     * It gives an overview over all chapters that belong to a lecture.
     *
     * @param $lecture_id is the id of the lecture of this view.
     * @return lecture.blade.php
     */
    public function showChapters($lecture_id) {

        if ($this->hasPermission($lecture_id)) {
            if ($this->lectureExists($lecture_id)) {
                $all_chapters = DB::table('chapter')->select('id', 'name')->where(['lecture_id' => $lecture_id])->get();
                $lecture = (array)DB::table('lecture')->select('id', 'name')->where(['id' => $lecture_id])->get()[0];

                $result = [];
                foreach ($all_chapters as $chapter) {
                    $result[] = (array)$chapter;
                }

                //Debugbar::warning($result);
                return view('lecture', compact('result', 'lecture', 'lecture_id'));
            } else {
                // TODO: chapter does not exist page.
                print "Sorry, but your requested chapter does not exist.";

            }
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
            $new_chapter_id = DB::table('chapter')->insertGetId(['lecture_id' => $lecture_id, 'name' => $chapter_name]);
            return redirect()->route('chapter', ['lecture_id' => $lecture_id, 'chapter_id' => $new_chapter_id]);
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
                    DB::table('chapter')->where(['id' => $chapter_to_remove_array[$x]])->delete();
                    DB::table('survey')->where(['chapter_id' => $chapter_to_remove_array[$x]])->delete();
                }

                // delete all questions which have a survey_id which is no longer available in table survey.
                $all_questions = DB::table('questions')->select('survey_id')->get();
                foreach ($all_questions as $question) {
                    $survey_exists =  sizeof(DB::table('survey')->where(['id' => $question->survey_id])->get()) > 0;
                    if (!$survey_exists) {
                        DB::table('questions')->where(['survey_id' => $question->survey_id])->delete();
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