<?php

namespace CaT\Plugins\MaterialList\Materials;

/**
 * Object class for material
 */
class Material
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $article_number;

    /**
     * @var string
     */
    protected $title;

    public function __construct($id, $article_number = "", $title = "")
    {
        assert('is_int($id)');
        assert('is_string($article_number)');
        assert('is_string($title)');

        $this->id = $id;
        $this->article_number = $article_number;
        $this->title = $title;
    }

    /**
     * Get the id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the article number
     *
     * @return string
     */
    public function getArticleNumber()
    {
        return $this->article_number;
    }

    /**
     * Get the title of article
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the article number
     *
     * @param string 	$article_number
     *
     * @return Material
     */
    public function withArticleNumber($article_number)
    {
        assert('is_string($article_number)');
        $clone = clone $this;
        $clone->article_number = $article_number;
        return $clone;
    }

    /**
     * Set the title
     *
     * @param string 	$title
     *
     * @return Material
     */
    public function withTitle($title)
    {
        assert('is_string($title)');
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }
}
