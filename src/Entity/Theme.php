<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableEntity;
use App\Entity\Traits\BlameableEntity;
use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a training theme that groups multiple cursus.
 *
 * A theme is a high-level category used to organize formations.
 * It includes metadata about creation and updates.
 */
#[ORM\Entity(repositoryClass: ThemeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Theme
{
    use TimestampableEntity;
    use BlameableEntity;

    /**
     * The unique identifier of the theme.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The name of the theme.
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Cursus> The cursus associated with this theme.
     */
    #[ORM\OneToMany(targetEntity: Cursus::class, mappedBy: 'theme')]
    private Collection $cursuses;

    /**
     * Constructor initializes the collection of cursus.
     */
    public function __construct()
    {
        $this->cursuses = new ArrayCollection();
    }

    /**
     * Get the ID of the theme.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the name of the theme.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the name of the theme.
     *
     * @param string $name
     * @return static
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get all cursus associated with this theme.
     *
     * @return Collection<int, Cursus>
     */
    public function getCursuses(): Collection
    {
        return $this->cursuses;
    }

    /**
     * Add a cursus to the theme.
     *
     * @param Cursus $cursus
     * @return static
     */
    public function addCursus(Cursus $cursus): static
    {
        if (!$this->cursuses->contains($cursus)) {
            $this->cursuses->add($cursus);
            $cursus->setTheme($this);
        }

        return $this;
    }

    /**
     * Remove a cursus from the theme.
     *
     * @param Cursus $cursus
     * @return static
     */
    public function removeCursus(Cursus $cursus): static
    {
        if ($this->cursuses->removeElement($cursus)) {
            if ($cursus->getTheme() === $this) {
                $cursus->setTheme(null);
            }
        }

        return $this;
    }
}
