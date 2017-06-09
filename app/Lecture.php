<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Chapter;

class lecture
{
    private $id;
    private $name;
    private $chapters;


    /**
     * lecture constructor.
     * @param $id
     */
    public function __construct($id, $name, Chapter $chapter)
    {
        $this->id = $id;
        $this->name = $name;
        // init chapters as array and add first chapter to this lecture.
        $this->chapters = [];
        $this->chapters[] = $chapter;
    }

    public function check_if_chapter_exists($chapter_id) {
        foreach ($this->getChapters() as $chapter) {
            if ($chapter->getId() == $chapter_id) {
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
     * @return array
     */
    public function getChapters()
    {
        return $this->chapters;
    }

    public function getChapterById($id) {
        foreach ($this->getChapters() as $chapter) {
            if ($chapter->getId() == $id) {
                return $chapter;
            }
        }
        return null;
    }

    /**
     * @param array $chapter
     */
    public function addChapter($chapter)
    {
        $this->chapters[] = $chapter;
    }
}
