<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class Collection implements Model, HasBanner, HasId, HasImpactStatement, HasSubjects, HasPublishedDate, HasThumbnail, HasUpdatedDate
{
    private $id;
    private $title;
    private $subTitle;
    private $impactStatement;
    private $publishedDate;
    private $updatedDate;
    private $banner;
    private $thumbnail;
    private $subjects;
    private $selectedCurator;
    private $selectedCuratorEtAl;
    private $curators;
    private $summary;
    private $content;
    private $relatedContent;
    private $podcastEpisodes;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title,
        PromiseInterface $subTitle,
        string $impactStatement = null,
        DateTimeImmutable $publishedDate,
        DateTimeImmutable $updatedDate = null,
        PromiseInterface $banner,
        Image $thumbnail,
        Sequence $subjects,
        Person $selectedCurator,
        bool $selectedCuratorEtAl,
        Sequence $curators,
        Sequence $summary,
        Sequence $content,
        Sequence $relatedContent,
        Sequence $podcastEpisodes
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->subTitle = $subTitle;
        $this->impactStatement = $impactStatement;
        $this->publishedDate = $publishedDate;
        $this->updatedDate = $updatedDate;
        $this->banner = $banner;
        $this->thumbnail = $thumbnail;
        $this->subjects = $subjects;
        $this->selectedCurator = $selectedCurator;
        $this->selectedCuratorEtAl = $selectedCuratorEtAl;
        $this->curators = $curators;
        $this->summary = $summary;
        $this->content = $content;
        $this->relatedContent = $relatedContent;
        $this->podcastEpisodes = $podcastEpisodes;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getSubTitle()
    {
        return $this->subTitle->wait();
    }

    public function getFullTitle() : string
    {
        return implode(': ', array_filter([$this->title, $this->getSubTitle()]));
    }

    /**
     * @return string|null
     */
    public function getImpactStatement()
    {
        return $this->impactStatement;
    }

    public function getPublishedDate() : DateTimeImmutable
    {
        return $this->publishedDate;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    public function getBanner() : Image
    {
        return $this->banner->wait();
    }

    public function getThumbnail() : Image
    {
        return $this->thumbnail;
    }

    /**
     * @return Sequence|Subject[]
     */
    public function getSubjects() : Sequence
    {
        return $this->subjects;
    }

    public function getSelectedCurator() : Person
    {
        return $this->selectedCurator;
    }

    public function selectedCuratorEtAl() : bool
    {
        return $this->selectedCuratorEtAl;
    }

    /**
     * @return Sequence|Person[]
     */
    public function getCurators() : Sequence
    {
        return $this->curators;
    }

    /**
     * @return Sequence|Block[]
     */
    public function getSummary() : Sequence
    {
        return $this->summary;
    }

    /**
     * @return Sequence|Model[]
     */
    public function getContent() : Sequence
    {
        return $this->content;
    }

    /**
     * @return Sequence|Model[]
     */
    public function getRelatedContent() : Sequence
    {
        return $this->relatedContent;
    }

    /**
     * @return Sequence|PodcastEpisode[]
     */
    public function getPodcastEpisodes() : Sequence
    {
        return $this->podcastEpisodes;
    }
}
