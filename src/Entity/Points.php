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

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="points",  fetch="EAGER")
     */
    private $user;

    public function getUser(): User {
        return $this->user;
    }

    public function setUser(User $user) {
        $this->user = $user;
    }

//    public function isLoggedUserAddedPoint(User $userToCompare): bool {
//        if($this->getUser() === $userToCompare){
//            return true;
//        }else{
//            return false;
//        }
//    }


    /**
     * @var Post
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="points",  fetch="EAGER")
     */
    private $post;

    public function getPost(): Post {
        return $this->post;
    }

    public function setPost(Post $post ){
        $this->post = $post;
    }

    /**
     * @var Comments
     * @ORM\ManyToOne(targetEntity="Comments", inversedBy="points",  fetch="EAGER")
     */
    private $comments;

    public function getComments(): Comments {
        return $this->comments;
    }

    public function setComments(Comments $comments ){
        $this->comments = $comments;
    }
//
//    public function getUser(): User {
//        return $this->post;
//    }
//
//    public function setUser(User $user) {
//        $this->user = $user;
//    }

//    /**
//     * @ORM\Column(type="integer")
//     */
//    private $post_Id;
//
//    /**
//     * @ORM\Column(type="integer")
//     */
//    private $UserId;

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getPost_Id(): ?int
//    {
//        return $this->post_Id;
//    }
//
//    public function setPost_Id(int $post_Id): self
//    {
//        $this->post_Id = $post_Id;
//
//        return $this;
//    }
//
//    public function getUserId(): ?int
//    {
//        return $this->UserId;
//    }
//
//    public function setUserId(int $UserId): self
//    {
//        $this->UserId = $UserId;
//
//        return $this;
//    }
}
