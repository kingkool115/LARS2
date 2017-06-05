<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 22.05.2017
 * Time: 22:16
 */

namespace App;


class Survey
{
    private $id;
    private $name;
    private $questions;
    private $chapter_id;

    /**
     * Survey constructor.
     * @param $id
     * @param $name
     * @param $chapter_id
     * @param $questions
     */
    public function __construct($id, $name, $chapter_id, $questions)
    {
        $this->id = $id;
        $this->name = $name;
        $this->questions = $questions;
        $this->chapter_id = $chapter_id;
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
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @param mixed $questions
     */
    public function setQuestions($questions)
    {
        $this->questions = $questions;
    }

    /**
     * @return mixed
     */
    public function getChapterId()
    {
        return $this->chapter_id;
    }

    /**
     * @param mixed $chapter_id
     */
    public function setChapterId($chapter_id)
    {
        $this->chapter_id = $chapter_id;
    }


}