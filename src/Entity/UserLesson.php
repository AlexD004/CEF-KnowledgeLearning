<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableEntity;
use App\Repository\UserLessonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity representing the relationship between a user and a lesson.
 *
 * This entity tracks ownership of a lesson by a user, and whether the user has validated the lesson.
 */
#[ORM\Entity(repositoryClass: UserLessonRepository::class)]
#[ORM\Table(name: 'user_lesson')]
#[ORM\HasLifecycleCallbacks]
class UserLesson
{
    use TimestampableEntity;

    /**
     * Unique identifier of the UserLesson entry.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * The user who owns or purchased the lesson.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?User $user = null;

    /**
     * The lesson associated with this entry.
     */
    #[ORM\ManyToOne(targetEntity: Lesson::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Lesson $lesson = null;

    /**
     * Indicates whether the user has validated this lesson.
     */
    #[ORM\Column(type: 'boolean')]
    private bool $isValidated = false;

    // ========================
    // Getters & Setters
    // ========================

    /**
     * Get the unique identifier.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the associated user.
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the associated user.
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
     * Get the associated lesson.
     *
     * @return Lesson|null
     */
    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    /**
     * Set the associated lesson.
     *
     * @param Lesson|null $lesson
     * @return self
     */
    public function setLesson(?Lesson $lesson): self
    {
        $this->lesson = $lesson;
        return $this;
    }

    /**
     * Check whether the lesson has been validated by the user.
     *
     * @return bool
     */
    public function isValidated(): bool
    {
        return $this->isValidated;
    }

    /**
     * Set the validation status of the lesson for the user.
     *
     * @param bool $isValidated
     * @return self
     */
    public function setIsValidated(bool $isValidated): self
    {
        $this->isValidated = $isValidated;
        return $this;
    }
}
