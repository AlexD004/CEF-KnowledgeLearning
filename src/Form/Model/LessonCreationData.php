<?php

namespace App\Form\Model;

use App\Entity\Theme;
use App\Entity\Cursus;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;

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
     */
    public bool $isNewTheme = false;

    /**
     * The selected Theme entity (if not creating a new one).
     */
    public ?Theme $selectedThemeId = null;

    /**
     * The name of the new Theme (if being created).
     */
    #[Assert\NotBlank(message: "Le nom du nouveau thème est requis", groups: ["new_theme"])]
    public ?string $newThemeName = null;

    /**
     * Optional image file for the new Theme.
     *
     * Used only if a new Theme is created.
     */
    public ?UploadedFile $newThemeImage = null;

    /**
     * Whether a new Cursus is being created instead of selecting an existing one.
     */
    public bool $isNewCursus = false;

    /**
     * The selected Cursus entity (if not creating a new one).
     */
    public ?Cursus $selectedCursusId = null;

    /**
     * The name of the new Cursus (if being created).
     */
    #[Assert\NotBlank(message: "Le nom du nouveau cursus est requis", groups: ["new_cursus"])]
    public ?string $newCursusName = null;

    /**
     * Optional image file for the new Cursus.
     *
     * Used only if a new Cursus is created.
     */
    public ?UploadedFile $newCursusImage = null;

    /**
     * The price of the new Cursus (if being created).
     */
    #[Assert\NotNull(message: "Le prix du nouveau cursus est requis", groups: ["new_cursus"])]
    #[Assert\PositiveOrZero(message: "Le prix du cursus doit être positif ou nul", groups: ["new_cursus"])]
    public ?float $newCursusPrice = null;

    /**
     * The name of the Lesson.
     */
    #[Assert\NotBlank(message: 'The lesson name is required.')]
    public ?string $lessonName = null;

    /**
     * The price of the Lesson.
     */
    #[Assert\NotNull(message: 'The price is required.')]
    #[Assert\PositiveOrZero(message: 'The price must be zero or positive.')]
    public ?float $lessonPrice = null;

    /**
     * The textual content of the Lesson.
     */
    #[Assert\NotBlank(message: 'Lesson text content is required.')]
    public ?string $contentText = null;

    /**
     * The video file uploaded for the lesson.
     *
     * This is stored in a protected folder and used for secured streaming access.
     */
    #[Assert\File(
        maxSize: '500M',
        mimeTypes: ['video/mp4', 'video/webm', 'video/ogg'],
        mimeTypesMessage: 'Please upload a valid video file (MP4, WebM, OGG).'
    )]
    public ?UploadedFile $videoFile = null;

    /**
     * Optional rich description for the lesson.
     */
    public ?string $description = null;

    /**
     * Optional uploaded image file for the lesson.
     */
    public ?UploadedFile $image = null;
}
