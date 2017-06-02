<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\Debugbar\Facade as Debugbar;


class ChapterController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function hasPermission($user_id, $chapter_id) {
        return sizeof(DB::table('survey')->where(['user_id' => $user_id, 'chapter_id' => $chapter_id])->get()) > 0;
    }

    /**
     * This function handles route /chapter/{chapter_id}/surveys.
     * It gives an overview over all surveys that belong to a chapter.
     *
     * @param $chapter_id is the id of the chapter the surveys belong to.
     * @return chapter.blade.php
     */
    public function showSurveys($chapter_id) {
        $user = Auth::user();

        if ($this->hasPermission($user['id'], $chapter_id)) {
            $all_surveys = DB::table('survey')->select('id', 'name')->where(['user_id' => $user['id'], 'chapter_id' => $chapter_id])->get();
            $chapter = (array)DB::table('chapter')->select('id', 'name')->where(['id' => $chapter_id])->get()[0];

            $result = [];
            foreach ($all_surveys as $survey) {
                $result[] = (array) $survey;
            }

            Debugbar::warning($result);
            return view('chapter', compact('result', 'chapter'));
        } else {
            // TODO: Permission denied page.
            print "Permission denied.";
        }
    }
}