<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Serializer\AddressNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;

final class PlaceNormalizerTest extends TestCase
{
    /** @var PlaceNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new PlaceNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new AddressNormalizer(),
        ]);
    }

    /**
     * @test
     */
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    /**
     * @test
     * @dataProvider canNormalizeProvider
     */
    public function it_can_normalize_places($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $place = new Place(['foo']);

        return [
            'place' => [$place, null, true],
            'place with format' => [$place, 'foo', true],
            'non-place' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_places(Place $place, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($place));
    }

    /**
     * @test
     */
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    /**
     * @test
     * @dataProvider canDenormalizeProvider
     */
    public function it_can_denormalize_places($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'place' => [[], Place::class, [], true],
            'non-place' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_places(Place $expected, array $json)
    {
        $actual = $this->normalizer->denormalize($json, Place::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $address = Builder::for(Address::class)->sample('simple');

        return [
            'complete' => [
                new Place(['place'], $address),
                [
                    'name' => ['place'],
                    'address' => [
                        'formatted' => ['address'],
                        'components' => [
                            'streetAddress' => ['street address'],
                            'locality' => ['locality'],
                            'area' => ['area'],
                            'country' => 'country',
                            'postalCode' => 'postal code',
                        ],
                    ],
                ],
            ],
            'minimum' => [
                $place = new Place(['place']),
                [
                    'name' => ['place'],
                ],
            ],
        ];
    }
}
