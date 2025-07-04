<?php

namespace App\Entity;

use App\Repository\UserCertificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Traits\TimestampableEntity;
use App\Entity\Cursus;
use App\Entity\User;

/**
 * Entity representing a validated cursus for a user.
 *
 * This certification is generated when a user completes all lessons
 * of a given cursus. It includes the date of validation (via createdAt).
 */
#[ORM\Entity(repositoryClass: UserCertificationRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'user_certification')]
class UserCertification
{
    use TimestampableEntity;

    /**
     * Unique identifier of the certification record.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * The user who completed the cursus.
     *
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?User $user = null;

    /**
     * The cursus that has been validated.
     *
     * @var Cursus|null
     */
    #[ORM\ManyToOne(targetEntity: Cursus::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Cursus $cursus = null;

    // ========================
    // Getters & Setters
    // ========================

    /**
     * Get the certification ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the user who owns the certification.
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the user who owns the certification.
     *
     * @param User|null $user
     * @return self
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the cursus that has been validated.
     *
     * @return Cursus|null
     */
    public function getCursus(): ?Cursus
    {
        return $this->cursus;
    }

    /**
     * Set the cursus that has been validated.
     *
     * @param Cursus|null $cursus
     * @return self
     */
    public function setCursus(?Cursus $cursus): self
    {
        $this->cursus = $cursus;
        return $this;
    }
}
