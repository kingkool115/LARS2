<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 24.05.2017
 * Time: 16:35
 */

namespace App\util;


class Question
{
    private $id;
    private $survey_id;
    private $question;
    private $image_path;                // if no image -> NULL
    // If it's a multiple choice question -> array will be filled with possible answers to display, MAX=array(7).
    // If it's a text response question -> array will be NULL.
    private $array_possible_answers;
    // If it's a multiple choice question -> array will be filled with booleans to define which possible answers where correct, MAX=array(7).
    // If it's a text response question -> array will be NULL.
    private $array_correct_answers;
    private $is_multi_select;           // If it's multiple choice and there are more correct answers.
    private $is_text_response;          // If it's not a multiple choice question
    private $correct_text_response;     // the correct answer to a text response question.
    private $show_result_on_next_slide;
    private $slide_number;

    /**
     * QuestionModel constructor.
     * @param $id                       id of this question in DB.
     * @param $survey_id                survey_id this question belongs to.
     * @param $question                 the question itself as text.
     * @param $image_path               the path to an image, if question has an image.
     * @param $array_possible_answers   string array of possible answers, if it's a multiple choice question.
     * @param $array_correct_answers    boolean array of correct answers, if it's a multiple choice question.
     * @param $is_multi_select          true if more than one answer in $array_possible_answers are correct.
     * @param $is_text_response         true if the it's not a multiple choice question, else false.
     * @param $correct_text_response    the correct answer to a non-multiple-choice-question.
     * @param $show_result_on_next_slide    true if the results of students should be shown on next slide of powerpoint presentation, false you want to see the result at the end.
     * @param $slide_number             the powerpoint slide number this question belongs to.
     */
    public function __construct($id, $survey_id, $question, $image_path,
                                $array_possible_answers, $array_correct_answers, $is_multi_select,
                                $is_text_response, $correct_text_response, $show_result_on_next_slide, $slide_number)
    {
        $this->id = $id;
        $this->survey_id = $survey_id;
        $this->question = $question;
        $this->image_path = $image_path;
        $this->array_possible_answers = $array_possible_answers;
        $this->array_correct_answers = $array_correct_answers;
        $this->is_multi_select = $is_multi_select;
        $this->is_text_response = $is_text_response;
        $this->correct_text_response = $correct_text_response;
        $this->show_result_on_next_slide = $show_result_on_next_slide;
        $this->slide_number = $slide_number;
    }

    /**
     * @return id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param id $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return survey_id
     */
    public function getSurveyId()
    {
        return $this->survey_id;
    }

    /**
     * @param survey_id $survey_id
     */
    public function setSurveyId($survey_id)
    {
        $this->survey_id = $survey_id;
    }

    /**
     * @return the
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param the $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * @return the
     */
    public function getImagePath()
    {
        return $this->image_path;
    }

    /**
     * @param the $image_path
     */
    public function setImagePath($image_path)
    {
        $this->image_path = $image_path;
    }

    /**
     * @return string
     */
    public function getArrayPossibleAnswers()
    {
        return $this->array_possible_answers;
    }

    /**
     * @param string $array_possible_answers
     */
    public function setArrayPossibleAnswers($array_possible_answers)
    {
        $this->array_possible_answers = $array_possible_answers;
    }

    /**
     * @return boolean
     */
    public function isArrayCorrectAnswers()
    {
        return $this->array_correct_answers;
    }

    /**
     * @param boolean $array_correct_answers
     */
    public function setArrayCorrectAnswers($array_correct_answers)
    {
        $this->array_correct_answers = $array_correct_answers;
    }

    /**
     * @return true
     */
    public function getIsMultiSelect()
    {
        return $this->is_multi_select;
    }

    /**
     * @param true $is_multi_select
     */
    public function setIsMultiSelect($is_multi_select)
    {
        $this->is_multi_select = $is_multi_select;
    }

    /**
     * @return true
     */
    public function getIsTextResponse()
    {
        return $this->is_text_response;
    }

    /**
     * @param true $is_text_response
     */
    public function setIsTextResponse($is_text_response)
    {
        $this->is_text_response = $is_text_response;
    }

    /**
     * @return the
     */
    public function getCorrectTextResponse()
    {
        return $this->correct_text_response;
    }

    /**
     * @param the $correct_text_response
     */
    public function setCorrectTextResponse($correct_text_response)
    {
        $this->correct_text_response = $correct_text_response;
    }

    /**
     * @return true
     */
    public function getShowResultOnNextSlide()
    {
        return $this->show_result_on_next_slide;
    }

    /**
     * @param true $show_result_on_next_slide
     */
    public function setShowResultOnNextSlide($show_result_on_next_slide)
    {
        $this->show_result_on_next_slide = $show_result_on_next_slide;
    }

    /**
     * @return the
     */
    public function getSlideNumber()
    {
        return $this->slide_number;
    }

    /**
     * @param the $slide_number
     */
    public function setSlideNumber($slide_number)
    {
        $this->slide_number = $slide_number;
    }


}