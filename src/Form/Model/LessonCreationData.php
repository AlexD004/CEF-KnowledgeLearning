<?php

namespace App\Form\Model;

use App\Entity\Theme;
use App\Entity\Cursus;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object (DTO) used to handle lesson creation
 * along with optional creation of a new Cursus and/or Theme.
 *
 * This object allows combining multiple entities in a single form
 * without requiring nested forms or complex entity binding.
 */
class LessonCreationData
{
    /**
     * Whether a new Theme is being created instead of selecting an existing one.
     *
     * @var bool
     */
    public bool $isNewTheme = false;

    /**
     * The selected Theme entity (if not creating a new one).
     *
     * @var Theme|null
     */
    public ?Theme $selectedThemeId = null;

    /**
     * The name of the new Theme (if being created).
     *
     * @var string|null
     */
    public ?string $newThemeName = null;

    /**
     * Whether a new Cursus is being created instead of selecting an existing one.
     *
     * @var bool
     */
    public bool $isNewCursus = false;

    /**
     * The selected Cursus entity (if not creating a new one).
     *
     * @var Cursus|null
     */
    public ?Cursus $selectedCursusId = null;

    /**
     * The name of the new Cursus (if being created).
     *
     * @var string|null
     */
    public ?string $newCursusName = null;

    /**
     * The price of the new Cursus (if being created).
     *
     * @var float|null
     */
    public ?float $newCursusPrice = null;

    /**
     * The name of the Lesson.
     *
     * @var string|null
     */
    #[Assert\NotBlank(message: 'The lesson name is required.')]
    public ?string $lessonName = null;

    /**
     * The price of the Lesson.
     *
     * @var float|null
     */
    #[Assert\NotNull(message: 'The price is required.')]
    #[Assert\PositiveOrZero(message: 'The price must be zero or positive.')]
    public ?float $lessonPrice = null;

    /**
     * The textual content of the Lesson.
     *
     * @var string|null
     */
    #[Assert\NotBlank(message: 'Lesson text content is required.')]
    public ?string $contentText = null;

    /**
     * The video URL for the Lesson.
     *
     * @var string|null
     */
    #[Assert\NotBlank(message: 'Video URL is required.')]
    #[Assert\Url(message: 'Please provide a valid video URL.')]
    public ?string $contentVideoUrl = null;

    /**
     * Optional rich description for the lesson.
     *
     * @var string|null
     */
    public ?string $description = null;

    /**
     * Optional image path or URL.
     *
     * @var string|null
     */
    #[Assert\Length(
        max: 255,
        maxMessage: 'Image path cannot exceed {{ limit }} characters.'
    )]
    public ?string $image = null;
}
