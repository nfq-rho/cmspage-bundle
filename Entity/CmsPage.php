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
use Nfq\AdminBundle\PlaceManager\Validator\Constraints as NfqPlaceAssert;
use Nfq\AdminBundle\Translatable\Entity\TranslatableTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
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
    use TranslatableTrait;

    /**
     * Variable to temporarily store path to old file
     *
     * @var string
     */
    private $tempImage;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=32)
     */
    protected $contentType;

    /**
     * @var string
     *
     * @Gedmo\Translatable()
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    protected $metaTitle;

    /**
     * @var string
     *
     * @Gedmo\Translatable()
     * @ORM\Column(type="string", length=155, nullable=true)
     */
    protected $metaDescription;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":0})
     */
    protected $isActive;

    /**
     * @var string[]
     *
     * @Gedmo\Translatable()
     * @ORM\Column(type="json", nullable=true)
     */
    protected $extra;

    /**
     * @var string
     *
     * @Gedmo\Translatable()
     * @ORM\Column(type="string", nullable=true)
     */
    protected $placeTitleOverwrite;

    /**
     * @var string[]
     *
     * @NfqPlaceAssert\HasEmptySlots(manager="Nfq\CmsPageBundle\Service\CmsPlaceManager")
     * @ORM\Column(type="json", nullable=true)
     */
    protected $places;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $isPublic;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, unique=true, options={"fixed":true})
     */
    protected $identifier;

    /**
     * @var string
     *
     * @Gedmo\Translatable()
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @Gedmo\Translatable()
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text;

    /**
     * @var string
     *
     * @Gedmo\Translatable()
     * @Gedmo\Slug(fields={"name"}, unique=true)
     * @ORM\Column(type="string", length=128, unique=true, nullable=true)
     */
    protected $slug;

    /**
     * Max file size is 5MB
     * @Assert\Image(maxSize="5242880", maxSizeMessage="cmspages.errors.file_too_large")
     */
    protected $file;

    /**
     * @var string
     *
     * @Gedmo\Translatable(fallback=true)
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $image;

    /**
     * @var string
     *
     * @Gedmo\Translatable()
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $imageAlt;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $sortPosition = 0;

    public function __construct()
    {
        $this->isActive = false;
        $this->extra = [];
        $this->places = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlaceTitleOverwrite(): ?string
    {
        return $this->placeTitleOverwrite;
    }

    public function setPlaceTitleOverwrite(string $placeTitleOverwrite): self
    {
        $this->placeTitleOverwrite = $placeTitleOverwrite;

        return $this;
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

    public function getContentType(): string
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

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file): self
    {
        if (null !== $this->image) {
            $this->tempImage = $this->image;
        }

        $this->file = $file;

        return $this;
    }

    public function resetTempFile(): void
    {
        $this->tempImage = null;
    }

    public function getTempFile(): ?string
    {
        return $this->tempImage;
    }

    public function getImage(): ?string
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

    public function setMetaDescription(string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaTitle(string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaTitle(): ?string
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

    public function getImageAlt(): ?string
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

    /**
     * @return string[]|null
     */
    public function getExtra(): ?array
    {
        return $this->extra;
    }

    /**
     * @param string[] $extra
     */
    public function setExtra(array $extra): self
    {
        $this->extra = $extra;

        return $this;
    }
}
