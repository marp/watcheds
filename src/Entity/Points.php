<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PointsRepository")
 */
class Points
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

//    /**
//     * @var Post
//     * @ORM\ManyToOne(targetEntity="Post", inversedBy="post",  fetch="EAGER")
//     */
//    private $post;
//
//    public function getUser(): User {
//        return $this->post;
//    }
//
//    public function setUser(User $user) {
//        $this->user = $user;
//    }

    /**
     * @ORM\Column(type="integer")
     */
    private $post_Id;

    /**
     * @ORM\Column(type="integer")
     */
    private $UserId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost_Id(): ?int
    {
        return $this->post_Id;
    }

    public function setPost_Id(int $post_Id): self
    {
        $this->post_Id = $post_Id;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->UserId;
    }

    public function setUserId(int $UserId): self
    {
        $this->UserId = $UserId;

        return $this;
    }
}
