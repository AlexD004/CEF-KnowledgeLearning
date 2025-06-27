<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableEntity;
use App\Repository\UserLessonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity representing a user's access to a purchased lesson.
 *
 * Each entry links a user to a lesson, indicating ownership and access rights.
 */
#[ORM\Entity(repositoryClass: UserLessonRepository::class)]
#[ORM\Table(name: 'user_lesson')]
#[ORM\HasLifecycleCallbacks]
class UserLesson
{
    use TimestampableEntity;

    /**
     * The unique identifier for the UserLesson entry.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * The user who has purchased or owns the lesson.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?User $user = null;

    /**
     * The lesson that is owned or purchased by the user.
     */
    #[ORM\ManyToOne(targetEntity: Lesson::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Lesson $lesson = null;

    // === Getters & Setters ===

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): self
    {
        $this->lesson = $lesson;
        return $this;
    }
}
