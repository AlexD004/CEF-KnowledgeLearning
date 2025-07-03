<?php

namespace App\Controller\Public;

use App\Entity\CartItem;
use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\UserLesson;
use App\Repository\CartItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Controller for managing shopping cart operations.
 *
 * Handles adding products (lessons or cursus) to the cart, ensuring business rules are respected.
 */
class CartController extends AbstractController
{
    /**
     * Display the user's shopping cart with item details and price breakdown.
     *
     * This includes both Lessons and Cursus items.
     * For Cursus, the method also calculates the total price of included lessons
     * to show a price comparison between individual lesson prices and the forfaitary price.
     *
     * @param CartItemRepository $cartItemRepository Repository to fetch cart items.
     * @param Security $security Used to get the currently authenticated user.
     * @return Response Rendered cart page.
     */
    #[Route('/panier', name: 'cart_show')]
    public function show(CartItemRepository $cartItemRepository, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            // If no user is authenticated, redirect to login page
            return $this->redirectToRoute('login');
        }

        // Retrieve all cart items associated with the user
        $cartItems = $cartItemRepository->findBy(['user' => $user]);

        $totalHT = 0;
        $TVA = 0.2; // 20% VAT
        $details = [];

        foreach ($cartItems as $item) {
            // If the item is a Lesson
            if ($item->getLesson()) {
                $lesson = $item->getLesson();
                $price = $lesson->getPrice();
                $totalHT += $price;

                $details[] = [
                    'type' => 'lesson',
                    'label' => $lesson->getName(),
                    'price' => $price,
                    'id' => $item->getId(),
                ];
            }

            // If the item is a Cursus
            elseif ($item->getCursus()) {
                $cursus = $item->getCursus();
                $cursusPrice = $cursus->getPrice();
                $lessons = $cursus->getLessons(); // Assume OneToMany relation

                $cumulativePrice = 0;
                $lessonsData = [];

                foreach ($lessons as $lesson) {
                    $cumulativePrice += $lesson->getPrice();
                    $lessonsData[] = [
                        'name' => $lesson->getName(),
                        'price' => $lesson->getPrice(),
                        'id' => $lesson->getId(),
                    ];
                }

                $totalHT += $cursusPrice;

                $details[] = [
                    'type' => 'cursus',
                    'label' => $cursus->getName(),
                    'price' => $cursusPrice,
                    'cumulative' => $cumulativePrice,
                    'lessons' => $lessonsData,
                    'id' => $item->getId(),
                ];
            }
        }

        $totalTTC = $totalHT;
        $totalHT = $totalTTC / (1 + $TVA);

        // Render the cart view with all calculated data
        return $this->render('cart/show.html.twig', [
            'items' => $details,
            'totalHT' => $totalHT,
            'TVA' => $TVA,
            'totalTTC' => $totalTTC,
        ]);
    }

    
    /**
     * Adds a Lesson or a Cursus to the current user's cart.
     *
     * This method prevents:
     * - Adding items already purchased
     * - Adding duplicate items already in the cart
     *
     * It supports both authenticated users and guests (to be extended with local storage).
     *
     * @param string $type The type of product to add: 'lesson' or 'cursus'
     * @param int $id The ID of the product to add
     * @param EntityManagerInterface $em Doctrine entity manager
     * @param Request $request HTTP request object
     *
     * @return Response Redirects to the cart view or displays error messages
     */
    #[Route('/panier/ajouter/{type}/{id}', name: 'cart_add', requirements: ['type' => 'lesson|cursus', 'id' => '\d+'])]
    public function add(string $type, int $id, EntityManagerInterface $em, Request $request): Response
    {
        $user = $this->getUser();
        $isConnected = $user !== null;

        // Step 1: Retrieve the product to add (Lesson or Cursus)
        $item = match ($type) {
            'lesson' => $em->getRepository(Lesson::class)->find($id),
            'cursus' => $em->getRepository(Cursus::class)->find($id),
            default => null
        };

        if (!$item) {
            throw $this->createNotFoundException("La formation sélectionnée n'existe pas.");
        }

        // Step 2: Check if the user has already purchased it
        if ($isConnected) {
            $alreadyOwned = false;

            if ($type === 'lesson') {
                $alreadyOwned = $em->getRepository(UserLesson::class)->findOneBy([
                    'user' => $user,
                    'lesson' => $item
                ]);
            } else {
                // For a cursus: all lessons must be already owned to skip the add
                $ownedLessons = $item->getLessons()->filter(function (Lesson $lesson) use ($user, $em) {
                    return $em->getRepository(UserLesson::class)->findOneBy([
                        'user' => $user,
                        'lesson' => $lesson
                    ]);
                });

                if ($ownedLessons->count() === $item->getLessons()->count()) {
                    $alreadyOwned = true;
                }
            }

            if ($alreadyOwned) {
                $this->addFlash('warning', 'Vous avez déjà accès à cette formation.');
                return $this->redirectToRoute('cart_show');
            }
        }

        // Step 3: Prevent duplicate items in cart
        $existing = $em->getRepository(CartItem::class)->findOneBy([
            'user' => $user,
            $type => $item
        ]);

        if ($existing) {
            $this->addFlash('info', 'Cette formation est déjà dans votre panier.');
            return $this->redirectToRoute('cart_show');
        }

        // Step 4: Add to cart
        $cartItem = new CartItem();
        $cartItem->setUser($user);

        if ($type === 'lesson') {
            $cartItem->setLesson($item);
        } else {
            $cartItem->setCursus($item);
        }

        $em->persist($cartItem);
        $em->flush(); // ✅ FLUSH IMMÉDIAT pour que la leçon soit visible dans les requêtes suivantes

        // STEP 5 — If all lessons from this cursus are now in the cart, replace with the cursus
        if ($type === 'lesson') {
            $lessonCursus = $item->getCursus();

            if ($lessonCursus) {
                $cursusId = $lessonCursus->getId();
                $cursusLessons = $lessonCursus->getLessons();
                $cursusLessonIds = [];

                foreach ($cursusLessons as $l) {
                    $cursusLessonIds[] = $l->getId();
                }

                // Get all lesson CartItems for this user
                $cartLessonItems = $em->getRepository(CartItem::class)->findBy([
                    'user' => $user,
                ]);

                $lessonIdsInCart = [];

                foreach ($cartLessonItems as $ci) {
                    $ciLesson = $ci->getLesson();
                    if ($ciLesson && $ciLesson->getCursus()?->getId() === $cursusId) {
                        $lessonIdsInCart[] = $ciLesson->getId();
                    }
                }

                sort($lessonIdsInCart);
                sort($cursusLessonIds);

                if ($lessonIdsInCart === $cursusLessonIds) {
                    // Remove all individual lessons
                    foreach ($cartLessonItems as $ci) {
                        if ($ci->getLesson() && $ci->getLesson()->getCursus()?->getId() === $cursusId) {
                            $em->remove($ci);
                        }
                    }

                    // Add the cursus
                    $cursusItem = new CartItem();
                    $cursusItem->setUser($user);
                    $cursusItem->setCursus($lessonCursus);
                    $em->persist($cursusItem);
                    $em->flush();

                    $this->addFlash('success', 'Certaines leçons ont été regroupées en un achat de cursus.');
                    return $this->redirectToRoute('cart_show');
                }
            }
        }


        $this->addFlash('success', 'La formation a bien été ajoutée au panier.');

        return $this->redirectToRoute('cart_show');
    }

    /**
     * Removes an item from the user's cart (lesson or cursus).
     *
     * @param int $id The ID of the CartItem to remove
     * @param CartItemRepository $cartItemRepository
     * @param EntityManagerInterface $em
     * @return Response Redirects back to the cart
     */
    #[Route('/panier/supprimer/{id}', name: 'cart_remove')]
    public function remove(int $id, CartItemRepository $cartItemRepository, EntityManagerInterface $em, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('remove_cartitem_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $cartItem = $cartItemRepository->find($id);

        if (!$cartItem || $cartItem->getUser() !== $this->getUser()) {
            $this->addFlash('warning', 'Élément introuvable ou non autorisé.');
            return $this->redirectToRoute('cart_show');
        }

        $em->remove($cartItem);
        $em->flush();

        $this->addFlash('success', 'Produit retiré du panier.');
        return $this->redirectToRoute('cart_show');
    }

    /**
     * Remove a single lesson from a CartItem representing a full Cursus.
     *
     * When a lesson is removed from a cursus in the cart, the entire CartItem for the
     * cursus is removed. The remaining lessons are re-added as individual CartItems.
     * This ensures that the user can selectively remove lessons within a cursus
     * while preserving the remaining ones in their cart.
     *
     * @param int $lessonId ID of the lesson to remove from the cursus
     * @param EntityManagerInterface $em Doctrine entity manager
     * @return Response Redirects to the cart page with updated items
     */
    #[Route('/panier/supprimer-lecon-de-cursus/{lessonId}', name: 'cart_remove_lesson_from_cursus', requirements: ['lessonId' => '\d+'])]
    public function removeLessonFromCursus(int $lessonId, EntityManagerInterface $em, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('remove_lesson_' . $lessonId, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $user = $this->getUser();
        $lesson = $em->getRepository(Lesson::class)->find($lessonId);

        if (!$user || !$lesson) {
            $this->addFlash('danger', 'Formation introuvable');
            return $this->redirectToRoute('cart_show');
        }

        $cursus = $lesson->getCursus();

        // Find the cursus CartItem for this user
        $cartItem = $em->getRepository(CartItem::class)->findOneBy([
            'user' => $user,
            'cursus' => $cursus
        ]);

        if (!$cartItem) {
            $this->addFlash('danger', 'Cursus introuvable dans votre panier.');
            return $this->redirectToRoute('cart_show');
        }

        // Remove the cursus CartItem
        $em->remove($cartItem);
        $em->flush();

        // Re-add all other lessons from that cursus
        foreach ($cursus->getLessons() as $otherLesson) {
            if ($otherLesson->getId() === $lessonId) {
                continue; // skip the one we're removing
            }

            // Prevent duplicate just in case
            $exists = $em->getRepository(CartItem::class)->findOneBy([
                'user' => $user,
                'lesson' => $otherLesson
            ]);

            if (!$exists) {
                $newLessonItem = new CartItem();
                $newLessonItem->setUser($user);
                $newLessonItem->setLesson($otherLesson);
                $em->persist($newLessonItem);
            }
        }

        $em->flush();

        $this->addFlash('success', 'La formation à bien été retirée de votre panier. Les autres formations ont été conservées.');
        return $this->redirectToRoute('cart_show');
    }

}
