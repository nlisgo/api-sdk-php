<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\Sequence;

final class Event implements Model, HasContent, HasId, HasImpactStatement, HasPublishedDate, HasUpdatedDate
{
    private $id;
    private $title;
    private $impactStatement;
    private $publishedDate;
    private $updatedDate;
    private $starts;
    private $ends;
    private $timeZone;
    private $uri;
    private $content;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title,
        string $impactStatement = null,
        DateTimeImmutable $publishedDate,
        DateTimeImmutable $updatedDate = null,
        DateTimeImmutable $starts,
        DateTimeImmutable $ends,
        DateTimeZone $timeZone = null,
        string $uri = null,
        Sequence $content
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->impactStatement = $impactStatement;
        $this->publishedDate = $publishedDate;
        $this->updatedDate = $updatedDate;
        $this->starts = $starts;
        $this->ends = $ends;
        $this->timeZone = $timeZone;
        $this->uri = $uri;
        $this->content = $content;
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

    public function getStarts() : DateTimeImmutable
    {
        return $this->starts;
    }

    public function getEnds() : DateTimeImmutable
    {
        return $this->ends;
    }

    /**
     * @return DateTimeZone|null
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * @return string|null
     */
    public function getUri()
    {
        return $this->uri;
    }

    public function getContent() : Sequence
    {
        return $this->content;
    }
}
