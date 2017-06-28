<?php

namespace App\Http\Controllers;

use App\ChapterModel;
use App\LectureModel;
use App\SurveyModel;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * This function checks if a user is permitted to use any functions of this class.
     *
     * @param $lecture_id does the user have the rights for this lecture_id?
     * @return bool true if user has permissions, false if not.
     */
    function hasPermission($lecture_id) {
        $user = Auth::user();
        return sizeof(DB::table('lecture')->where(['user_id' => $user['id'], 'id' => $lecture_id])->get()) > 0;
    }

    /**
     * This function checks if constellation of route to
     * lecture/{lecture_id}/chapter/[chapter_id}/survey/{survey_id}/slide_number/{slide_number} with given parameters is correct.
     * Check if lecture, chapter, survey, slide_number belong to each other.
     *
     * @param $lecture_id       id of the lecture
     * @param $chapter_id       id of the chapter
     * @param $survey_id        id of the survey
     * @return bool             true if constellation of the parameters in url exists, else false.
     */
    function checkLectureDependencies($lecture_id, $chapter_id, $survey_id) {
        $lecture_exists = LectureModel::where(['id' => $lecture_id])->count() > 0;
        $chapter_exists = ChapterModel::where(['id' => $chapter_id])->count() > 0;
        $survey_exists = SurveyModel::where(['id' => $survey_id])->count() > 0;
        if ($lecture_exists && $chapter_exists && $survey_exists) {
            return true;
        }
        return false;
    }
}
