<?php

namespace eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;

final class ReportReference implements Reference, HasDoi
{
    private $id;
    private $date;
    private $discriminator;
    private $authors;
    private $authorsEtAl;
    private $title;
    private $publisher;
    private $doi;
    private $pmid;
    private $isbn;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        Date $date,
        string $discriminator = null,
        array $authors,
        bool $authorsEtAl,
        string $title,
        Place $publisher,
        string $doi = null,
        int $pmid = null,
        string $isbn = null,
        string $uri = null
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->discriminator = $discriminator;
        $this->authors = $authors;
        $this->authorsEtAl = $authorsEtAl;
        $this->title = $title;
        $this->publisher = $publisher;
        $this->doi = $doi;
        $this->pmid = $pmid;
        $this->isbn = $isbn;
        $this->uri = $uri;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getDate() : Date
    {
        return $this->date;
    }

    public function getDiscriminator()
    {
        return $this->discriminator;
    }

    /**
     * @return AuthorEntry[]
     */
    public function getAuthors() : array
    {
        return $this->authors;
    }

    public function authorsEtAl(): bool
    {
        return $this->authorsEtAl;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPublisher() : Place
    {
        return $this->publisher;
    }

    /**
     * @return string|null
     */
    public function getDoi()
    {
        return $this->doi;
    }

    /**
     * @return int|null
     */
    public function getPmid()
    {
        return $this->pmid;
    }

    /**
     * @return string|null
     */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /**
     * @return string|null
     */
    public function getUri()
    {
        return $this->uri;
    }
}
