<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 22.05.2017
 * Time: 22:13
 */

namespace App\util;


class Chapter
{
    private $id;
    private $name;
    private $surveys;

    /**
     * Chapter constructor.
     * @param $id
     * @param $name
     * @param $survey
     */
    public function __construct($id, $name, $survey)
    {
        $this->id = $id;
        $this->name = $name;
        // add a survey to list of surveys
        $this->surveys = [];
        $this->surveys[] = $survey;
    }

    public function check_if_survey_exists($survey_id) {
        foreach ($this->getSurveys() as $survey) {
            if ($survey->getId() == $survey_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getSurveys()
    {
        return $this->surveys;
    }

    /**
     * @param mixed $survey
     */
    public function addSurvey($survey)
    {
        $this->surveys[] = $survey;
    }
}