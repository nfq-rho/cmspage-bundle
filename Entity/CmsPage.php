<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CmsPageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Nfq\AdminBundle\PlaceManager\Validator\Constraints as NfqPlaceAssert;

/**
 * CmsPage
 *
 * @ORM\Table(name="cmspage", indexes={
 *      @ORM\Index(name="type_idx", columns={"content_type"}),
 *      @ORM\Index(name="sort_position_idx", columns={"sort_position"}),
 * })
 * @ORM\Entity(repositoryClass="Nfq\CmsPageBundle\Repository\CmsPageRepository")
 * @UniqueEntity(fields={"slug"}, message="admin.cmspage.errors.field_not_unique")
 * @UniqueEntity(fields={"identifier"}, message="admin.cmspage.errors.field_not_unique")
 * @Gedmo\TranslationEntity(class="Nfq\CmsPageBundle\Entity\CmsPageTranslation")
 */
class CmsPage
{
    /**
     * Variable to temporarily store path to old file
     *
     * @var string
     */
    private $tempImage;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="content_type", type="string", length=32)
     */
    protected $contentType;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="meta_title", type="string", length=55, nullable=true)
     */
    protected $metaTitle;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="meta_description", type="string", length=155, nullable=true)
     */
    protected $metaDescription;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":0})
     */
    protected $isActive;

    /**
     * @var array
     * @ORM\Column(type="json", nullable=true)
     */
    private $extra;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(type="string", nullable=true)
     */
    protected $placeTitleOverwrite;

    /**
     * @var string[]
     * @NfqPlaceAssert\HasEmptySlots(manager="Nfq\CmsPageBundle\Service\CmsPlaceManager")
     * @ORM\Column(type="json", nullable=true)
     */
    private $places;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isPublic;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, unique=true, options={"fixed":true})
     */
    protected $identifier;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @Gedmo\Slug(fields={"name"}, unique=true)
     * @ORM\Column(type="string", length=128, unique=true, nullable=true)
     */
    protected $slug;

    /**
     * Max file size is 5MB
     * @Assert\Image(maxSize="5242880", maxSizeMessage="cmspages.errors.file_too_large")
     */
    private $file;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $image;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $imageAlt;

    /**
     * @var string
     *
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $sortPosition = 0;

    public function __construct()
    {
        $this->isActive = false;
        $this->extra = [];
        $this->places = [];
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPlaceTitleOverwrite()
    {
        return $this->placeTitleOverwrite;
    }

    /**
     * @param string $placeTitleOverwrite
     */
    public function setPlaceTitleOverwrite($placeTitleOverwrite)
    {
        $this->placeTitleOverwrite = $placeTitleOverwrite;
    }

    /**
     * @return string[]
     */
    public function getPlaces(): array
    {
        return $this->places;
    }

    public function setPlaces(array $places): self
    {
        $this->places = $places;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier
     *
     * @return mixed $identifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get name
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get text
     *
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set text
     *
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file): self
    {
        if (isset($this->image)) {
            $this->tempImage = $this->image;
        }

        $this->file = $file;

        return $this;
    }

    public function resetTempFile()
    {
        $this->tempImage = null;
    }

    /**
     * @return string
     */
    public function getTempFile()
    {
        return $this->tempImage;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    public function setMetaDescription(string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * Get metaDescription
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    public function setMetaTitle(string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    public function getIsPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * @return string
     */
    public function getImageAlt()
    {
        return $this->imageAlt;
    }

    public function setImageAlt(string $imageAlt): self
    {
        $this->imageAlt = $imageAlt;

        return $this;
    }

    public function getSortPosition(): int
    {
        return $this->sortPosition;
    }

    public function setSortPosition(int $sortPosition): self
    {
        $this->sortPosition = $sortPosition;

        return $this;
    }

    public function getExtra(): array
    {
        return $this->extra;
    }

    public function setExtra(array $extra): self
    {
        $this->extra = $extra;

        return $this;
    }
}
