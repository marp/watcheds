<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
//use Doctrine\ORM\Persisters\Collection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 */
class Post
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="post",  fetch="EAGER")
     */
    private $user;

    public function getUser(): User {
        return $this->user;
    }

    public function setUser(User $user) {
        $this->user = $user;
    }
//
//    /**
//     * Add user
//     *
//     * @param \App\Entity\User $user
//     *
//     * @return Post
//     */
//    public function addUser(User $user)
//    {
//        $this->user[] = $user;
//
//        return $this;
//    }

    /**
     * @var Comments
     * @ORM\OneToMany(targetEntity="Comments", mappedBy="post", fetch="EAGER")
     */
    private $comments;

    /**
     * @return Collection|Comments[]
     */
    public function getComments(): Collection {
        return $this->comments;
    }

    /**
     * @var Points
     * @ORM\OneToMany(targetEntity="Points", mappedBy="post", fetch="EAGER")
     */
    private $points;

    /**
     * @return Collection|Points[]
     */
    public function getPoints(): Collection {
        return $this->points;
    }


//    /**
//     * @var Post
//     * @ORM\OneToMany(targetEntity="Post", mappedBy="id")
//     */
//    private $post;




    /**
     * @ORM\Column(type="string", length=9000, nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

}
