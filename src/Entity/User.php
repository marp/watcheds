<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
//use Elastica\Search;

//use Symfony\Component\Serializer\Annotation\Groups;


//@Search(repositoryClass="AppBundle\SearchRepository\UserRepository")

/**
 * @UniqueEntity(fields="email", message="Email already taken")
 * @UniqueEntity(fields="username", message="Username already taken")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Post
     * @ORM\OneToMany(targetEntity="Post", mappedBy="user")
     */
    private $post;

    /**
     * @var Points
     * @ORM\OneToMany(targetEntity="Points", mappedBy="user")
     */
    private $points;

    /**
     * @var Comments
     * @ORM\OneToMany(targetEntity="Comments", mappedBy="user")
     */
    private $comments;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 4,
     *      max = 50,
     *      minMessage = "Your username must be at least {{ limit }} characters long",
     *      maxMessage = "Your username cannot be longer than {{ limit }} characters"
     * )
     * @Assert\Regex(
     *      pattern = "/^[a-z0-9][a-z0-9_\-]+$/i",
     *      htmlPattern = "^[a-z]+$",
     *      message = "The login may contain uppercase and lowercase letters, numbers, and - and _ characters, but must begin with a letter or number."
     * )
     */
    private $username;
//* @Groups({"elastica"})
//* @var string

    /**
     * @Assert\NotBlank()
    //     * @Assert\Length(max=4096)
     * @Assert\Length(
     *      min = 4,
     *      max = 80,
     *      minMessage = "Your password must be at least {{ limit }} characters long",
     *      maxMessage = "Your password cannot be longer than {{ limit }} characters"
     * )
     */
    private $plainPassword;

    /**
     * The below length depends on the "algorithm" you use for encoding
     * the password, but this works well with bcrypt.
     *
     * @ORM\Column(type="string", length=64)
     */
    private $password;


    /**
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $creationdatetime;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $visibility;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\File(
     *     mimeTypes={ "image/x-png,image/gif,image/jpeg" },
     *     mimeTypesMessage="Invalid image!",
     *     maxSize="1024k",
     *     maxSizeMessage="The maximum file size is 1 MB"
     * )
     */
    private $avatar;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : 0})
     */
    private $verified;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    public function __construct()
    {
        $this->roles = array('ROLE_USER');
    }

    // other properties and methods

    public function getId()
    {
        return $this->id;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getSalt()
    {
        // The bcrypt and argon2i algorithms don't require a separate salt.
        // You *may* need a real salt if you choose a different encoder.
        return null;
    }

    public function getRoles()
    {
        return $this->roles;
    }


    public function eraseCredentials()
    {
    }

    public function getCreationdatetime(): ?\DateTimeInterface
    {
        return $this->creationdatetime;
    }

    public function setCreationdatetime(?\DateTimeInterface $creationdatetime): self
    {
        $this->creationdatetime = $creationdatetime;

        return $this;
    }

    public function getVisibility(): ?int
    {
        return $this->visibility;
    }

    public function setVisibility(?int $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }

    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(?bool $verified): self
    {
        $this->verified = $verified;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}