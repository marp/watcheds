<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Episodes
 *
 * @ORM\Table(name="episodes")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\EpisodesRepository")
 */
class Episodes
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
     * @ORM\Column(name="parentTconst", type="text", length=65535, nullable=true)
     */
    private $parenttconst;

    /**
     * @var string|null
     *
     * @ORM\Column(name="seasonNumber", type="text", length=65535, nullable=true)
     */
    private $seasonnumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="episodeNumber", type="text", length=65535, nullable=true)
     */
    private $episodenumber;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTconst(): ?string
    {
        return $this->tconst;
    }


    public function getSeasonNumber(): ?int
    {
        return $this->seasonnumber;
    }

    public function getEpisodeNumber(): ?int
    {
        return $this->episodenumber;
    }

}
