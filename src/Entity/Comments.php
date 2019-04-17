<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentsRepository")
 */
class Comments
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var post
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="comments",  fetch="EAGER")
     */
    private $post;

//    /**
//     * @ORM\Column(type="integer", nullable=true)
//     */
//    private $user_id;




    public function getPost(): Post {
        return $this->post;
    }

    public function setPost(Post $post) {
        $this->post = $post;
    }

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="comments",  fetch="EAGER")
     */
    private $user;

    public function getUser(): User {
        return $this->user;
    }

    public function setUser(User $user) {
        $this->user = $user;
    }


    /**
     * @var Points
     * @ORM\OneToMany(targetEntity="Points", mappedBy="comments", fetch="EAGER")
     */
    private $points;

    /**
     * @return Collection|Points[]
     */
    public function getPoints(): Collection {
        return $this->points;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

//    /**
//     * @ORM\Column(type="integer")
//     */
//    private $postid;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $visible;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser_id(): ?int
    {
        return $this->user_id;
    }

    public function setUser_id(int $user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
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
//
//    public function getPostid(): ?int
//    {
//        return $this->postid;
//    }
//
//    public function setPostid(int $postid): self
//    {
//        $this->postid = $postid;
//
//        return $this;
//    }

    public function getVisible(): ?int
    {
        return $this->visible;
    }

    public function setVisible(?int $visible): self
    {
        $this->visible = $visible;

        return $this;
    }
}
