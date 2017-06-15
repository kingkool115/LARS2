<?php
/**
 * Created by PhpStorm.
 * User: george
 * Date: 15.06.17
 * Time: 09:45
 */

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\PushBots\PushBots;

class ApiController extends Controller
{

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth.basic');
    }

    /**
     * Handles route /api/lectures.
     * Returns all lectures of a user.
     *
     * @return lectures in json format.
     */
    public function getAllLectures() {
        $user = Auth::user();
        $lectures = DB::table('lecture')->select('id', 'name')->where(['user_id' => $user['id']])->get();
        return response()->json($lectures);
    }

    /**
     * Handles route /api/lecture/{lecture_id?}/chapters.
     * Returns all chapters of a certain lecture.
     *
     * @param $lecture_id id of lecture.
     * @return chapters in json format.
     */
    public function getAllChaptersOfLecture($lecture_id) {
        $user = Auth::user();
        $chapters = DB::table('chapter')->select('id', 'name')->where(['lecture_id' => $lecture_id])->get();
        $has_permission = DB::table('lecture')->where(['user_id' => $user['id'], 'id' => $lecture_id])->get()->count() > 0;
        if ($has_permission) {
            return response()->json($chapters);
        } else {
            return response('Permission denied', 403);
        }
    }

    /**
     * Handles route /api/lecture/{lecture_id?}/chapter/{chapter_id?}/surveys.
     * Returns all surveys of a certain chapter.
     *
     * @param $lecture_id id of lecture
     * @param $chapter_id id of chapter.
     *
     * @return surveys in json format.
     */
    public function getAllSurveysOfChapter($lecture_id, $chapter_id) {
        $user = Auth::user();
        $has_permission = DB::table('lecture')->where(['user_id' => $user['id'], 'id' => $lecture_id])->get()->count() > 0;
        $chapters_result  = DB::table('chapter')->select('id')->where(['lecture_id' => $lecture_id])->get();

        if ($has_permission) {
            foreach ($chapters_result as $chapter) {
                if ($chapter->id == $chapter_id) {
                    $surveys = DB::table('survey')->select('id', 'name')->where(['chapter_id' => $chapter_id])->get();
                    return response()->json($surveys);
                }
            }
            return response("No surveys found for given parameters", 404);
        } else {
            return response('Permission denied', 403);
        }
    }

    /**
     * Handles route /api/push/lecture/{lecture_id?}/chapter/{chapter_id?}/survey/{survey_id}/question/{slide_number}.
     * Pushes a certain question to the devices which have subscribed for that lecture.
     *
     * @param $lecture_id   id of the lecture.
     * @param $chapter_id   id of the chapter.
     * @param $survey_id    id of the survey.
     * @param $slide_number slide number the question belongs to.
     *
     * @return
     */
    public function pushQuestion($lecture_id, $chapter_id, $survey_id, $slide_number) {
        if ($this->hasPermission($lecture_id)) {
            if ($this->checkUrlConstellation($lecture_id, $chapter_id, $survey_id)) {
                // Push The notification with parameters
                $pb = new PushBots();
                // Application ID
                $appID = '58ff58814a9efa8b758b4567';
                // Application Secret
                $appSecret = '01b7aa1b97cd22430683efd3e4f9a8d6';
                $pb->App($appID, $appSecret);
                // Notification Settings
                $pb->Alert("Halo i bims!!");
                $pb->Platform(1);
                // android
                // Push it !
                $res = $pb->Push();
                print $res['status'];
                print $res['code'];
                print $res['data'];
            } else {
                return "Wrong url constellation. This lecture-chapter-survey-slide_number relation does not exist.";
            }
        } else {
            return "Permission denied.";
        }
    }
}