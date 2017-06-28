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
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
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
     * This function checks whether a slide_number of the current presentation contains question or not.
     *
     * @param $survey_id    survey_id of the current running survey.
     * @param $slide_number slide_number that belongs to that survey and has to be checked for a question.
     * @return True if slide_number has a question. Else false.
     */
    function slideNumberHasQuestion($survey_id, $slide_number) {
        return DB::table('questions')->where(['survey_id' => $survey_id, 'slide_number' => $slide_number])->get()->count() > 0;
    }


    /**
     * This function is called by api/switch_slide/lecture/{lecture_id}/chapter/{chapter_id}/survey/{survey_id}/slide_number/{slide_number}.
     *
     * @param $lecture_id
     * @param $chapter_id
     * @param $survey_id
     * @param $slide_number
     * @return
     */
    protected function switchSlide($lecture_id, $chapter_id, $survey_id, $slide_number) {
        if ($this->hasPermission($lecture_id) && $this->checkLectureDependencies($lecture_id, $chapter_id, $survey_id)) {

            Request::session()->put('time_of_last_slide', Carbon::now()->toDateTimeString());
            Request::session()->put('current_slide', $slide_number);

            // if slide_number has a question, than push question
            if ($this->slideNumberHasQuestion($survey_id, $slide_number)) {
                // return if pushing notification was successful (200OK) or not.
                return $this->pushQuestion($lecture_id, $survey_id, $slide_number)['status'];
                // TODO: tell PPT if question results will be displayed on next slide or at the end.
            }
            // if a question is pushed at the first slide then init like:
            // Request::session()->put('pushed_notifications_for_slides', [$slide_number]);
            // else
            Request::session()->put('pushed_notifications_for_slides', []);
            return Request::session()->all();
        }
        return "You have no permissions to start that survey.";
    }

    /**
     * Handles route /api/push/lecture/{lecture_id?}/chapter/{chapter_id?}/survey/{survey_id}/question/{slide_number}.
     * Pushes a certain question to the devices which have subscribed for that lecture.
     *
     * @param $lecture_id    only submit to devices which have subscribed for this id.
     * @param $survey_id    needed to identify correct question which should be pushed.
     * @param $slide_number needed to identify correct question which should be pushed.
     *
     * @return
     */
    function pushQuestion($lecture_id, $survey_id, $slide_number) {
        // Push The notification with parameters
        $pb = new PushBots();
        // Application ID
        $appID = '58ff58814a9efa8b758b4567';
        // Application Secret
        $appSecret = '01b7aa1b97cd22430683efd3e4f9a8d6';
        $pb->App($appID, $appSecret);


        // get correct question for the students from DB
        $question = (array)DB::table('questions')->where(['survey_id' => $survey_id, 'slide_number' => $slide_number])->get()[0];

        // 'lIco' is used to display image already in push notification, if there is any.
        $question['lIco'] = $question['image_path'];

        // Push The notification with parameters
        // Notification Settings
        $pb->Payload($question);
        // send only to user's who have subscribed for this lecture
        $pb->Tags([$lecture_id]);
        // The title when the push notification appears
        $pb->Alert($question['question']);
        $pb->Platform(1);
        // android
        // Push it !
        $res = $pb->Push();
        print $res['status'];
        print $res['code'];
        print $res['data'];
        return $res;
    }
}