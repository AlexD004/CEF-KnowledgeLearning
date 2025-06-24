<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableEntity;
use App\Entity\Traits\BlameableEntity;
use App\Repository\CursusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a training program (cursus) composed of several lessons.
 *
 * A cursus is linked to a Theme, has a name, a price, and contains multiple lessons.
 * It includes timestamps and authorship metadata.
 */
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: CursusRepository::class)]
class Cursus
{
    use TimestampableEntity;
    use BlameableEntity;

    /**
     * The unique identifier of the cursus.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The name/title of the cursus.
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

     /**
     * The image filename associated with the cursus.
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * The price of the cursus in euros.
     */
    #[ORM\Column]
    private ?float $price = null;

    /**
     * The theme (category) to which the cursus belongs.
     */
    #[ORM\ManyToOne(inversedBy: 'cursuses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Theme $theme = null;

    /**
     * @var Collection<int, Lesson> The lessons contained within this cursus.
     */
    #[ORM\OneToMany(targetEntity: Lesson::class, mappedBy: 'cursus')]
    private Collection $lessons;

    /**
     * Constructor initializes the lessons collection.
     */
    public function __construct()
    {
        $this->lessons = new ArrayCollection();
    }

    /**
     * Get the ID of the cursus.
     *
     * @return int|null The ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the name of the cursus.
     *
     * @return string|null The name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the name of the cursus.
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
     * Get the image filename.
     *
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * Set the image filename.
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
     * Get the price of the cursus.
     *
     * @return float|null The price in euros
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Set the price of the cursus.
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
     * Get the theme associated with the cursus.
     *
     * @return Theme|null The theme
     */
    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    /**
     * Set the theme for this cursus.
     *
     * @param Theme|null $theme The theme to associate
     * @return static
     */
    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Get the collection of lessons in this cursus.
     *
     * @return Collection<int, Lesson>
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    /**
     * Add a lesson to this cursus.
     *
     * @param Lesson $lesson The lesson to add
     * @return static
     */
    public function addLesson(Lesson $lesson): static
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
            $lesson->setCursus($this);
        }

        return $this;
    }

    /**
     * Remove a lesson from this cursus.
     *
     * @param Lesson $lesson The lesson to remove
     * @return static
     */
    public function removeLesson(Lesson $lesson): static
    {
        if ($this->lessons->removeElement($lesson)) {
            if ($lesson->getCursus() === $this) {
                $lesson->setCursus(null);
            }
        }

        return $this;
    }
}
