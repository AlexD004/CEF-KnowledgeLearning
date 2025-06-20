<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Traits\TimestampableEntity;
use App\Entity\Traits\BlameableEntity;
use App\Repository\LessonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a lesson within a cursus.
 *
 * A lesson contains text and video content, a price, and can be marked as validated.
 * It is linked to a Cursus entity and includes audit fields (timestamps and authors).
 */
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: LessonRepository::class)]
class Lesson
{
    use TimestampableEntity;
    use BlameableEntity;

    /**
     * The unique identifier of the lesson.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The title or name of the lesson.
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * The price of the lesson in euros.
     */
    #[ORM\Column]
    private ?float $price = null;

    /**
     * The textual content of the lesson.
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $contentText = null;

    /**
     * The URL pointing to the video content of the lesson.
     */
    #[ORM\Column(length: 255)]
    private ?string $contentVideoUrl = null;

    /**
     * The cursus to which this lesson belongs.
     */
    #[ORM\ManyToOne(inversedBy: 'lessons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cursus $cursus = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    /**
     * Optional rich description of the lesson.
     *
     * This may contain formatted text (CKEditor).
     *
     * @var string|null
     */
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    /**
     * Image path or URL associated with the lesson.
     *
     * Used to visually represent the lesson in listings or detail pages.
     *
     * @var string|null
     */
    private ?string $image = null;

    /**
     * Indicates whether the lesson has been validated/completed.
     */
    #[ORM\Column(type: 'boolean')]
    private bool $isValidated = false;

    /**
     * Get the ID of the lesson.
     *
     * @return int|null The lesson ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the name of the lesson.
     *
     * @return string|null The lesson name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the name of the lesson.
     *
     * @param string $name The name to set
     * @return static
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the price of the lesson.
     *
     * @return float|null The price in euros
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Set the price of the lesson.
     *
     * @param float $price The price to set
     * @return static
     */
    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get the text content of the lesson.
     *
     * @return string|null The text content
     */
    public function getContentText(): ?string
    {
        return $this->contentText;
    }

    /**
     * Set the text content of the lesson.
     *
     * @param string $contentText The content to set
     * @return static
     */
    public function setContentText(string $contentText): static
    {
        $this->contentText = $contentText;
        return $this;
    }

    /**
     * Get the video URL of the lesson.
     *
     * @return string|null The video URL
     */
    public function getContentVideoUrl(): ?string
    {
        return $this->contentVideoUrl;
    }

    /**
     * Set the video URL of the lesson.
     *
     * @param string $contentVideoUrl The video URL to set
     * @return static
     */
    public function setContentVideoUrl(string $contentVideoUrl): static
    {
        $this->contentVideoUrl = $contentVideoUrl;
        return $this;
    }

    /**
     * Get the cursus this lesson is associated with.
     *
     * @return Cursus|null The associated cursus
     */
    public function getCursus(): ?Cursus
    {
        return $this->cursus;
    }

    /**
     * Set the cursus this lesson is associated with.
     *
     * @param Cursus|null $cursus The associated cursus
     * @return static
     */
    public function setCursus(?Cursus $cursus): static
    {
        $this->cursus = $cursus;
        return $this;
    }

    /**
     * Get the rich text description of the lesson.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the rich text description of the lesson.
     *
     * @param string|null $description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the lesson's image path or URL.
     *
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * Set the lesson's image path or URL.
     *
     * @param string|null $image
     * @return self
     */
    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Check if the lesson is validated.
     *
     * @return bool True if validated, false otherwise
     */
    public function isValidated(): bool
    {
        return $this->isValidated;
    }

    /**
     * Set the lesson as validated or not.
     *
     * @param bool $isValidated Whether the lesson is validated
     * @return static
     */
    public function setIsValidated(bool $isValidated): static
    {
        $this->isValidated = $isValidated;
        return $this;
    }
}
