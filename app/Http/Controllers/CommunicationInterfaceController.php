<?php
/**
 * Created by PhpStorm.
 * User: george
 * Date: 14.07.17
 * Time: 08:08
 */

namespace App\Http\Controllers;


use App\AnswerModel;
use App\ChapterModel;
use App\EvaluateQuestionsMcModel;
use App\EvaluateQuestionsModel;
use App\LectureModel;
use App\PresentationSessionModel;
use App\PushedQuestionModel;
use App\QuestionModel;
use App\SubscriptionModel;
use App\User;
use App\PushBots\PushBots;

/**
 * This controller handles all requests between this web server, a powerpoint presentation session and
 * the the android devices of the students.
 * **/
class CommunicationInterfaceController extends Controller {

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('api');
    }

    /**
     * Decrypt message in /decrypt/{message}
     **/
    public function decryptExample($message) {
        //return $message;
        $encrypted = encrypt("Hello World");
        return $encrypted;
    }

    /**
     * Check if given session id is valid.
     *
     * @param $session_id
     * @return true if session exists and is active, else false.
     **/
    private function sessionExistsAndActive($session_id) {
        return PresentationSessionModel::
            where(['id' => $session_id, 'active' => true])->count() == 1;
    }

    /**
     * Check if given student subscribed for this lecture.
     *
     * @param $lecture_id id of lecture.
     * @return true if student is subscribed, else false.
     * */
    private function studentSubscribed($student_id, $lecture_id) {
        return SubscriptionModel::where(['student_id' => $student_id, 'lecture_id' => $lecture_id])->count() > 0;
    }

    /**
     * Gets the image url of an images.
     *
     * @param $question a question instance
     * @return url
     * **/
    private function getImageUrlOfQuestion($question) {
        if ($question->image_path == null) {
            return null;
        } else {
            $url = route('public_image', ['file_name' => $question->image_path]);
            // TODO: workaround to display picture on android device
            $url = str_replace('localhost', '192.168.178.26', $url);
            $url = str_replace('127.0.0.1', '192.168.178.26', $url);
            return $url;
        }
    }
    
    /**
     * Get all available Lectures from all users.
     * This is needed when a user without an account wants to start a powerpoint presentation.
     * He selects the correct lecture so that the students get the questions if they subscribed for that lectures.
     *
     * @return all available lectures from db.
     * */
    public function getAllAvailableLectures() {
        if (request()->isJson()) {
            $user = User::where(['email' => request()->input("user_email")])->first();
            if (isset($user)) {
                return response()->json(LectureModel::all()->toArray());
            }
            return response("No permission to get all lectures", 403);
        } else {
            return  response('Bad Request', 400);
        }
    }

    /**
     * Get all lectures of a chapter without being logged in.
     *
     * @return a list of chapters of a certain lecture
     * **/
    public function getChaptersOfLectureNoAuth($lecture_id) {
        if (request()->isJson()) {
            $user = User::where(['email' => request()->input("user_email")])->first();
            if (isset($user)) {
                return response()->json(ChapterModel::where(["lecture_id" => $lecture_id])->get()->toArray());
            }
            return response("No permission to get all lectures", 403);
        } else {
            return  response('Bad Request', 400);
        }
    }

    /**
     * Handles route /subscribe/
     *    * -> encrypted json
     * {
     *      lecture_ids: [3,4,5],
     *      student_id: 5
     * }
     *
     * Insert a student into subscription table.
     *
     * @return HttpStatusCode with a short message.
     **/
    public function subscribe() {
        // TODO: check if pushbots id (student id) exists before subscribing
        // TODO: im json content ist die student_id verschlüsselt enthalten
        if (request()->isJson()) {
            $lecture_ids = request()->input("lecture_ids");
            $student_id = request()->input('student_id');

            foreach ($lecture_ids as $lecture_id) {
                $subscription_model = new SubscriptionModel();
                $subscription_model->lecture_id = $lecture_id;
                $subscription_model->student_id = $student_id;
                $subscription_model->save();
            }
            return response('Successfully subscribed', 200);
        }
        return response('Bad Request', 400);
    }

    /**
     * Handles route /unsubscribe
     *    * -> encrypted json
     * {
     *      lecture_ids: [3,4,5],
     *      student_id: 5
     * }
     *
     * Insert a student into subscription table.
     * @return HttpStatusCode with a message.
     **/
    public function unsubscribe() {
        if (request()->isJson()) {
            $lecture_ids = request()->input("lecture_ids");
            $student_id = request()->input('student_id');

            foreach ($lecture_ids as $lecture_id) {
                $subscriptionExists = SubscriptionModel::where(['lecture_id' => $lecture_id, 'student_id' => $student_id])
                        ->count() == 1;
                if ($subscriptionExists) {
                    SubscriptionModel::where(['lecture_id' => $lecture_id, 'student_id' => $student_id])->delete();
                }
            }
            return response('Unsubscribed', 200);
        }
        return response('Bad Request', 400);
    }

    /**
     * Handles route /start_presentation_session.
     * -> encrypted json request
     * {
     *      lecture_id: 1,
     *      chapter_id: 2,
     *      session_id: 457dfs7s6dr,
     *      user_email: presentation_user
     * }
     *
     * Just insert values into presentation_session table.
     */
    public function startPresentationSession(){
        if (request()->isJson()) {
            $user = User::where(['email' => request()->input("user_email")])->first();
            $session_id = request()->input('session_id');
            $lecture_id = request()->input('lecture_id');

            if (!isset($user)) {
                    return response('Not allowed to start presentation', 403);
            }

            if (PresentationSessionModel::where(['id' => $session_id])->count() > 0) {
                return response('Presentation session already exists.', 409);
            }

            // when a user without account wants to start the presentation.
            if (!isset($lecture_id)) {
                $presentation_session = new PresentationSessionModel();
                $presentation_session->user_id = $user->id;
                $presentation_session->lecture_id = null;
                $presentation_session->chapter_id = null;
                $presentation_session->id = $session_id;
                $presentation_session->active = true;
                $presentation_session->save();
                return response('Presentation session started', 200);
            }

            $presentation_session = new PresentationSessionModel();
            $presentation_session->user_id = $user->id;
            $presentation_session->lecture_id = $lecture_id;
            $presentation_session->id = $session_id;
            $presentation_session->active = true;
            $presentation_session->save();
            return response('Presentation session started', 200);
        }
        return response('Bad Request', 400);
    }

    /**
     * Handles route /push_question
     * -> encrypted json request
     * {
     *      user_email: "presentation_user"
     *      lecture_id: 6,
     *      session_id: 785,
     *      question_id: 9,
     * }
     *
     * Insert values to pushed_question table.
     * Make PushBots send the questions to students.
     */
    public function pushQuestion(){

        $lecture_id = request()->input("lecture_id");
        $session_id = request()->input('session_id');
        $question_id = request()->input("question_id");
        $user = User::where(['email' => request()->input("user_email")])->first();

        //return response()->json(['lecture' => $lecture_id, 'session_id' => $session_id, 'question_id' => $question_id, 'user' => $user], 200);
        if ($user == null) {
            return response('No permission to push questions.', 403);
        }

        if (PresentationSessionModel::where(['id' => $session_id])->count() == 0) {
            return response('Session does not exist.', 404);
        }

        if (PresentationSessionModel::where(['active' => false])->count() > 0) {
            return response('Presentation is not active anymore.', 403);
        }

        if (LectureModel::where(['id' => $lecture_id])->count() == 0) {
            return response('Can not push question about this lecture because lecture does not exist.', 404);
        }

        if (QuestionModel::where(['id' => $question_id])->count() == 0) {
            return response('The question you want to push does not exist.', 404);
        }

        if (PushedQuestionModel::where(['question_id' => $question_id, 'session_id' => $session_id])->count() > 0) {
            return response('Question was already pushed for this session', 409);
        }

        if (request()->isJson() && $this->sessionExistsAndActive($session_id)) {

            $result = [];

            // field session_id
            $result['session_id'] = $session_id;

            // field question
            $question_dict = [];
            $question = QuestionModel::where(["id" => $question_id])->first();
            $question_dict['id'] = $question->id;
            $question_dict['lecture_id'] = $lecture_id;
            $question_dict['question'] = $question->question;
            $question_dict['question_uid'] = uniqid();
            $question_dict['is_text_response'] = $question->is_text_response;
            $question_dict['is_multi_select'] = $question->is_multi_select;
            $question_dict['image_path'] = $this->getImageUrlOfQuestion($question);

            $result['question'] = $question_dict;

            // field answers
            $answers_list = [];
            $answers = AnswerModel::where(['question_id' => $question_id])->get();
            foreach ($answers as $answer) {
                $answer_dict = [];
                $answer_dict['id'] = $answer->id;
                $answer_dict['answer'] = $answer->answer;
                $answer_dict['is_correct'] = $answer->is_correct;
                $answers_list[] = $answer_dict;
            }

            $result['answers'] = $answers;

            // push json to all android devices
            $this->pushBotpush($result);

            // insert into pushed_question table
            $pushed_question_model = new PushedQuestionModel();
            $pushed_question_model->lecture_id = $result['question']['lecture_id'];
            $pushed_question_model->session_id = $session_id;
            // needed to identify a question when opening it by notification on android phone
            $pushed_question_model->question_uid = $result['question']['question_uid'];
            $pushed_question_model->question_id = $question_id;
            $pushed_question_model->save();


            return response()->json('Pushed question to android devices.', 200);
        }
        return response('Bad Request', 400);
    }

    /**
     * Push the question to subscribed android devices.
     *
     * @param $result: is a json message like this:
     *
     * {
     *      "session_id": "49898ds4a",
     *      "question" : {
     *                       "id": "9",
     *                      "lecture_id": "1"
     *                      "question": "3+3?",
     *                      "is_text_response": false,
     *                      "image_path": "fileshare.com/5433f344ws"
     *                   },
     *      "answers" : [
     *                      {
     *                           "id": "75",
     *                          "answer" : "9",
     *                          "is_correct": false
     *                      },
     *                      {
     *                           "id": "76",
     *                          "answer": "6",
     *                          "is_correct": true
     *                      }
     *                  ]
     * }
     *
     * @return
     */
    function pushBotpush($result) {
        // Push The notification with parameters
        $pb = new PushBots();
        // Application ID
        $appID = '58ff58814a9efa8b758b4567';
        // Application Secret
        $appSecret = '01b7aa1b97cd22430683efd3e4f9a8d6';
        $pb->App($appID, $appSecret);

        // 'lIco' is used to display image already in push notification, if there is any.
        if (isset($result['question']['image_path'])) {
            $result['bigPicture'] = $result['question']['image_path'];
        }

        // Set lecture name as notification title
        $lecture = LectureModel::where(["id" => $result['question']['lecture_id']])->first();
        $result['nTitle'] = $lecture->name;

        // Push The notification with parameters
        // Notification Settings
        $pb->Payload($result);

        // send only to user's who have subscribed for this lecture
        $pb->Tags($result['question']['lecture_id']);

        // The title when the push notification appears
        $pb->Alert($result['question']['question']);

        // android
        $pb->Platform(1);

        // Push it !
        $res = $pb->Push();

        print $res['status'];
        print $res['code'];
        print $res['data'];
        return $res;
    }

    /**
     * Handles route /answer_question
     * -> encrypted json request for multiple choice answer
     * {
     *      "lecture_id": "1",
     *      "student_id": "321",
     *      "session_id": "457dfs7s6dr",
     *      "question_id": "6",
     *      "is_text_response": "false",
     *      "answer_ids": "6"
     * }
     *
     * -> encrypted json request for text response answer
     * {
     *      "lecture_id": "1",
     *      "student_id": "321",
     *      "session_id": "457dfs7s6dr",
     *      "question_id": "1",
     *      "is_text_response": "true",
     *      "answer": "richtig .."
     * }
     *
     * Receive answer from student.
     */
    public function answerQuestion(){
        $lecture_id = request()->input("lecture_id");
        $student_id = request()->input("student_id");
        $session_id = request()->input('session_id');
        $question_id = request()->input("question_id");
        $is_text_response = request()->input("is_text_response");

        if (!$this->studentSubscribed($student_id, $lecture_id)) {
            return response("Answer received. You are not allowed to answer this question", 403);
        }

        if (PushedQuestionModel::where(['question_id' => $question_id, 'session_id' => $session_id])->count() == 0) {
            return response("Answer received. There is no pushed question for your given answer.", 404);
        }

        if (PresentationSessionModel::where(['id' => $session_id, 'active' => false])->count() == 1) {
            return response("Answer received. Session for this question is already closed.", 200);
        }

        if (request()->isJson() && $this->sessionExistsAndActive($session_id)) {
            // insert into evaluate_questions table
            $evaluate_questions_model = new EvaluateQuestionsModel();
            $evaluate_questions_model->student_id = $student_id;
            $evaluate_questions_model->session_id = $session_id;
            $evaluate_questions_model->question_id = $question_id;
            $evaluate_questions_model->is_text_response = $is_text_response;
            $evaluate_questions_model->answer = request()->input("answer");
            $evaluate_questions_model->save();
            return response("Answer received.", 200);
        }
        return response()->json("Answer not sent.", 400);
    }

    /**
     * Handles route /evaluate_answers
     * -> encrypted json request
     * {
     *      "session_id": "457dfs7s6dr",
     *      "question_id": [6,1]
     * }
     *
     * ->encrypted json response
     * {
     *      questions: [
     *          {
                    "question_id": 4,
     *              "is_text_response": true,
     *              "answers":
     *                  [
     *                      "answer1": 3,
     *                      "answer2": 6,
     *                      "answer3": 4
     *                  ]
     *          },
     *          {
                    "question_id": 5,
     *              "is_text_response": false,
     *              "answers":
     *                  [
     *                      "answer1": 4,
     *                      "answer2": 8,
     *                      "answer3": 7
     *                  ]
     *          }
     *      ]
     * }
     **/
    public function evaluateAnswers() {
        $session_id = request()->input('session_id');
        $question_ids = request()->input("question_ids");
        if (isset($question_ids)) {
            $question_ids = explode(',', $question_ids);
        }
        $result = array();

        if (request()->isJson() && $this->sessionExistsAndActive($session_id)) {
            // iterate through all given question_ids
            foreach ($question_ids as $question_id) {

                if (PushedQuestionModel::where(['question_id' => $question_id, 'session_id' => $session_id])->count() == 0) {
                    continue;
                }

                // add to result
                $result[] = $this->getAnswersOfOneQuestion($question_id, $session_id);
            }

            return response()->json($result);
        }
    }


    /**
     * Handles route /get_answers_of_one_question/{question_id}/{session_id}
     * Get evaluation of one single question.
     *
     * @param $question_id id of question
     * @param $session_id id of session.
     * @return this:
     *
     *          {
                    "question_id": 5,
     *              "is_text_response": false,
     *              "answers":
     *                  [
     *                      "answer1": 4,
     *                      "answer2": 8,
     *                      "answer3": 7
     *                  ]
     *          }
     */
    public function getAnswersOfOneQuestion($question_id, $session_id) {

        // iterate mc questions
        $answers = AnswerModel::where(['question_id' => $question_id])->get();
        $question_eval = [];
        $question_eval['question_id'] = $question_id;
        $question = QuestionModel::where(['id' => $question_id])->first();
        $question_eval['question'] = $question->question;
        $question_eval['image_path'] = $this->getImageUrlOfQuestion($question);

        // it's a text response question
        if ($question->is_text_response) {
            $eval_tr_answers = EvaluateQuestionsModel::where(['question_id' => $question_id, "session_id" => $session_id])->get();
            $answers_for_this_question = [];

            // iterate answers from evaluation table
            foreach ($eval_tr_answers as $tr_answer) {

                // iterate already collected answers
                foreach ($answers_for_this_question as $key => $value) {
                    // this answer already exists in collected answers -> increment counter
                    if ($tr_answer->answer == $key) {
                        $answers_for_this_question[$tr_answer->answer] += 1;
                    }
                }

                // if answer was not found in already collected answers -> make a new entry with counter = 1
                if (!isset($answers_for_this_question[$tr_answer->answer])) {
                    $answers_for_this_question[$tr_answer->answer] = 1;
                }
            }
            $question_eval['is_text_response'] = true;
            arsort($answers_for_this_question);
            $question_eval['answers'] = $answers_for_this_question;
        // it's a multiple choice question
        } else {
            $eval_mc_answers = EvaluateQuestionsModel::where(['question_id' => $question_id, "session_id" => $session_id])->get();
            $answers_for_this_question = [];

            // fill answers for this question with all possible answers and set their counter to zero
            foreach ($answers as $answer) {
                $answers_for_this_question[$answer->answer] = 0;
            }

            // iterate all multiple choice answers from each student
            foreach ($eval_mc_answers as $mc_answer) {
                // if it's a multiple choice question the answer is composed of answer ids seperated by commas -> 27,6,8
                $mc_ids = explode(',', $mc_answer->answer);
                // iterate all given answer ids of the student for this question
                foreach ($mc_ids as $id) {

                    // iterate the answers that were init before
                    foreach ($answers as $answer) {

                        // student selected this answer -> increment its counter
                        if ($answer->id == $id) {
                            $answers_for_this_question[$answer->answer] += 1;
                        }
                    }
                }
            }
            arsort($answers_for_this_question);
            $question_eval['is_text_response'] = false;
            $question_eval['answers'] = $answers_for_this_question;
        }

        return $question_eval;
    }
}