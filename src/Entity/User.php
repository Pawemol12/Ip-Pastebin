<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Serializable;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="last_login_date")
     */
    private $lastLoginDate;

    /**
    * @ORM\OneToMany(targetEntity="Paste", mappedBy="user")
    */
    private $pastes;

    /**
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return array_unique(array_merge(['ROLE_USER'], $this->roles));
    }

    public function setRoles(array $roles)
    {
        $this->roles = array_unique(array_merge(['ROLE_USER'], $roles));
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeInterface|null $createdAt
     */
    public function setCreatedAt(?DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getLastLoginDate(): ?DateTimeInterface
    {
        return $this->lastLoginDate;
    }

    /**
     * @param DateTimeInterface|null $lastLoginDate
     */
    public function setLastLoginDate(?DateTimeInterface $lastLoginDate): void
    {
        $this->lastLoginDate = $lastLoginDate;
    }

    /**
     *
     * @return string
     */
    public function serialize(): string {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->createdAt,
            $this->lastLoginDate,
            $this->roles
        ]);
    }

    /**
     *
     * @param mixed $serialized
     */
    public function unserialize($serialized): void {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->createdAt,
            $this->lastLoginDate,
            $this->roles
            ) = unserialize($serialized, ['allowed_classes' => false]);
    }

    /**
     * @return mixed
     */
    public function getPastes()
    {
        return $this->pastes;
    }

    /**
     * @param mixed $pastes
     */
    public function setPastes($pastes): void
    {
        $this->pastes = $pastes;
    }

    /**
     * Add paste
     * @param Paste $paste
     */
    public function addPaste(Paste $paste)
    {
        if ($this->pastes->contains($paste)) {
            return;
        }

        $this->pastes[] = $paste;
        $paste->setUser($this);
    }

    /**
     * Remove paste
     * @param Paste $paste
     */
    public function removePaste(Paste $paste)
    {
        if (!$this->pastes->contains($paste)) {
            return;
        }

        $this->pastes->removeElement($paste);
        $paste->setUser($this);
    }
}
