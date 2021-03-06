<?php

namespace eLife\ApiSdk\Model;

final class PersonAuthor extends Author
{
    private $person;
    private $deceased;

    /**
     * @internal
     */
    public function __construct(
        PersonDetails $person,
        bool $deceased = false,
        array $additionalInformation = [],
        array $affiliations = [],
        string $competingInterests = null,
        string $contribution = null,
        array $emailAddresses = [],
        array $equalContributionGroups = [],
        array $phoneNumbers = [],
        array $postalAddresses = []
    ) {
        parent::__construct($additionalInformation, $affiliations, $competingInterests, $contribution, $emailAddresses,
            $equalContributionGroups, $phoneNumbers, $postalAddresses);

        $this->person = $person;
        $this->deceased = $deceased;
    }

    public function toString() : string
    {
        return $this->getPreferredName();
    }

    public function getPreferredName() : string
    {
        return $this->person->getPreferredName();
    }

    public function getIndexName() : string
    {
        return $this->person->getIndexName();
    }

    /**
     * @return string|null
     */
    public function getOrcid()
    {
        return $this->person->getOrcid();
    }

    public function isDeceased() : bool
    {
        return $this->deceased;
    }
}
