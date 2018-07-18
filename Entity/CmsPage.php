<?php

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
 *      @ORM\Index(name="type_idx", columns={"content_type"})
 * })
 * @ORM\Entity(repositoryClass="Nfq\CmsPageBundle\Entity\CmsPageRepository")
 * @UniqueEntity(fields={"slug"}, message="cmspage.errors.field_not_unique")
 * @UniqueEntity(fields={"identifier"}, message="cmspage.errors.field_not_unique")
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
     * @var string
     *
     * @ORM\Column(name="active", type="boolean", options={"default":0}, nullable=true)
     */
    protected $isActive;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="place_name", type="string", nullable=true)
     */
    protected $placeName;

    /**
     * @var array $places
     * @NfqPlaceAssert\HasEmptySlots(manager="nfq_cmspage.service.place_manager")
     * @ORM\Column(name="place", type="simple_array", nullable=true)
     */
    private $places;

    /**
     * @var string
     *
     * @ORM\Column(name="public", type="boolean")
     */
    private $isPublic;

    /**
     * @var string
     *
     * @ORM\Column(name="identifier", type="string", length=50, unique=true, options={"fixed":true})
     */
    protected $identifier;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="text", type="text", nullable=true)
     */
    protected $text;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @Gedmo\Slug(fields={"name"}, unique=true)
     * @ORM\Column(name="slug", type="string", length=128, unique=true, nullable=true)
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
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    protected $image;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="image_alt", type="string", length=255, nullable=true)
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
     * Added in case Country uses multiple locales and has different content by locale
     * 
     * @var string|null
     *
     * @ORM\Column(name="country_code", type="string", length=2, nullable=true)
     */
    protected $countryCode;

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
    public function getPlaceName()
    {
        return $this->placeName;
    }

    /**
     * @param string $placeName
     */
    public function setPlaceName($placeName)
    {
        $this->placeName = $placeName;
    }

    /**
     * @return array
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * @param array $places
     * @return $this
     */
    public function setPlaces($places)
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

    /**
     * @param mixed $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Set identifier
     *
     * @param $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
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

    /**
     * Set name
     *
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
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

    /**
     * Set slug
     *
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
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

    /**
     * Set file
     *
     * @param UploadedFile $file
     * @return $this
     */
    public function setFile(UploadedFile $file)
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

    /**
     * @param mixed $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return $this
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Is Active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->getIsActive();
    }

    /**
     * @param $locale
     */
    public function setLocale($locale)
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

    /**
     * Set metaDescription
     *
     * @param string $metaDescription
     *
     * @return $this
     */
    public function setMetaDescription($metaDescription)
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

    /**
     * Set title
     *
     * @param string $metaTitle
     *
     * @return $this
     */
    public function setMetaTitle($metaTitle)
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

    /**
     * @return string
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * @param string $isPublic
     *
     * @return $this
     */
    public function setIsPublic($isPublic)
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

    /**
     * @param string $imageAlt
     *
     * @return CmsPage
     */
    public function setImageAlt($imageAlt)
    {
        $this->imageAlt = $imageAlt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     *
     * @return CmsPage
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }
}
