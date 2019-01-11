<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Titles
 *
 * @ORM\Table(name="titles")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\TitlesRepository")
 */
class Titles
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tconst", type="text", length=65535, nullable=true)
     */
    private $tconst;

    /**
     * @var string|null
     *
     * @ORM\Column(name="titleType", type="text", length=65535, nullable=true)
     */
    private $titletype;

    /**
     * @var string|null
     *
     * @ORM\Column(name="primaryTitle", type="text", length=65535, nullable=true)
     */
    private $primarytitle;

    /**
     * @var string|null
     *
     * @ORM\Column(name="originalTitle", type="text", length=65535, nullable=true)
     */
    private $originaltitle;

    /**
     * @var string|null
     *
     * @ORM\Column(name="isAdult", type="text", length=65535, nullable=true)
     */
    private $isadult;

    /**
     * @var string|null
     *
     * @ORM\Column(name="startYear", type="text", length=65535, nullable=true)
     */
    private $startyear;

    /**
     * @var string|null
     *
     * @ORM\Column(name="endYear", type="text", length=65535, nullable=true)
     */
    private $endyear;

    /**
     * @var string|null
     *
     * @ORM\Column(name="runtimeMinutes", type="text", length=65535, nullable=true)
     */
    private $runtimeminutes;

    /**
     * @var string|null
     *
     * @ORM\Column(name="genres", type="text", length=65535, nullable=true)
     */
    private $genres;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTconst(): ?string
    {
        return $this->tconst;
    }


    public function getPrimarytitle(): ?string
    {
        return $this->primarytitle;
    }

    public function getStartYear(): ?string
    {
        return $this->startyear;
    }

    public function getEndYear(): ?string
    {
        return $this->endyear;
    }

    public function getGenres(): ?string
    {
        return $this->genres;
    }

}
