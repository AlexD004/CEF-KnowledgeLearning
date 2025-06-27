<?php

namespace App\Entity;

use App\Repository\CartItemRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\TimestampableEntity;

/**
 * Represents an item in the user's shopping cart.
 * 
 * A cart item can be either a single Lesson or an entire Cursus,
 * but never both at the same time.
 * 
 * Timestamps are handled via the TimestampableEntity trait.
 */
#[ORM\Entity(repositoryClass: CartItemRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CartItem
{
    use TimestampableEntity;

    /**
     * Unique identifier for the cart item.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The user who owns the cart item (nullable for guest carts).
     *
     * @var User|null
     */
    #[ORM\ManyToOne(inversedBy: 'cartItems')]
    private ?User $user = null;

    /**
     * The individual lesson added to the cart (nullable).
     *
     * @var Lesson|null
     */
    #[ORM\ManyToOne]
    private ?Lesson $lesson = null;

    /**
     * The full cursus added to the cart (nullable).
     *
     * @var Cursus|null
     */
    #[ORM\ManyToOne]
    private ?Cursus $cursus = null;

    /**
     * Get the ID of the cart item.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the user who owns the cart item.
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the user who owns the cart item.
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
     * Get the lesson associated with this cart item.
     *
     * @return Lesson|null
     */
    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    /**
     * Set the lesson associated with this cart item.
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
     * Get the cursus associated with this cart item.
     *
     * @return Cursus|null
     */
    public function getCursus(): ?Cursus
    {
        return $this->cursus;
    }

    /**
     * Set the cursus associated with this cart item.
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
