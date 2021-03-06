<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiClient\ApiClient\PodcastClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\PodcastEpisodeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class PodcastEpisodeNormalizerTest extends ApiTestCase
{
    /** @var PodcastEpisodeNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new PodcastEpisodeNormalizer(new PodcastClient($this->getHttpClient()));
        $this->normalizer->setNormalizer($apiSdk->getSerializer());
        $this->normalizer->setDenormalizer($apiSdk->getSerializer());
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
    public function it_can_normalize_podcast_episodes($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $podcastEpisode = new PodcastEpisode(1, 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null, rejection_for('No banner'), Builder::dummy(Image::class),
            [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
            new PromiseSequence(rejection_for('Subjects should not be unwrapped')),
            new PromiseSequence(rejection_for('Chapters should not be unwrapped')));

        return [
            'podcast episode' => [$podcastEpisode, null, true],
            'podcast episode with format' => [$podcastEpisode, 'foo', true],
            'non-podcast episode' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_podcast_episodes(PodcastEpisode $podcastEpisode, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($podcastEpisode, null, $context));
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
    public function it_can_denormalize_podcast_episodes($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'podcast episode' => [[], PodcastEpisode::class, [], true],
            'podcast episode by type' => [['type' => 'podcast-episode'], Model::class, [], true],
            'non-podcast episode' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_podcast_episodes(
        PodcastEpisode $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, PodcastEpisode::class, null, $context);

        $this->mockSubjectCall('1');
        $this->mockSubjectCall('subject1');
        $this->mockArticleCall('1', !empty($context['complete']), false, 1);
        $this->mockArticleCall('14107', !empty($context['complete']), false, 1);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $published = new DateTimeImmutable('yesterday', new DateTimeZone('Z'));
        $updated = new DateTimeImmutable('now', new DateTimeZone('Z'));
        $banner = Builder::for(Image::class)->sample('banner');
        $thumbnail = Builder::for(Image::class)->sample('thumbnail');

        return [
            'complete' => [
                new PodcastEpisode(1, 'Podcast episode 1 title', 'Podcast episode 1 impact statement', $published, $updated,
                    promise_for($banner), $thumbnail,
                    [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
                    new EmptySequence(), new ArraySequence([
                        new PodcastEpisodeChapter(1, 'Chapter title', 0, 'Chapter impact statement',
                            new ArraySequence([
                                Builder::for(ArticlePoA::class)
                                    ->withTitlePrefix('title prefix')
                                    ->withPdf('http://www.example.com/')
                                    ->withSubjects(new ArraySequence([
                                        Builder::for(Subject::class)
                                            ->withId('subject1')
                                            ->__invoke(),
                                    ]))
                                    ->withResearchOrganisms(['research organism'])
                                    ->__invoke(),
                                Builder::for(Collection::class)->sample('tropical-disease'),
                            ])),
                    ])),
                ['complete' => true],
                [
                    'number' => 1,
                    'title' => 'Podcast episode 1 title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                    'image' => [
                        'thumbnail' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
                            ],
                        ],
                        'banner' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/banner.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/banner.jpg/full/full/0/default.jpg',
                                'filename' => 'banner.jpg',
                            ],
                            'size' => [
                                'width' => 1800,
                                'height' => 900,
                            ],
                        ],
                    ],
                    'sources' => [
                        [
                            'mediaType' => 'audio/mpeg',
                            'uri' => 'https://www.example.com/episode.mp3',
                        ],
                    ],
                    'chapters' => [
                        [
                            'number' => 1,
                            'title' => 'Chapter title',
                            'time' => 0,
                            'content' => [
                                [
                                    'id' => '14107',
                                    'stage' => 'published',
                                    'version' => 1,
                                    'type' => 'research-article',
                                    'doi' => '10.7554/eLife.14107',
                                    'authorLine' => 'Yongjian Huang et al',
                                    'title' => 'Molecular basis for multimerization in the activation of the epidermal growth factor',
                                    'volume' => 5,
                                    'elocationId' => 'e14107',
                                    'published' => '2016-03-28T00:00:00Z',
                                    'versionDate' => '2016-03-28T00:00:00Z',
                                    'statusDate' => '2016-03-28T00:00:00Z',
                                    'titlePrefix' => 'title prefix',
                                    'pdf' => 'http://www.example.com/',
                                    'subjects' => [
                                        ['id' => 'subject1', 'name' => 'Subject 1'],
                                    ],
                                    'researchOrganisms' => ['research organism'],
                                    'abstract' => [
                                        'content' => [
                                            [
                                                'type' => 'paragraph',
                                                'text' => 'Article 14107 abstract text',
                                            ],
                                        ],
                                    ],
                                    'status' => 'poa',
                                ],
                                [
                                    'id' => 'tropical-disease',
                                    'type' => 'collection',
                                    'title' => 'Tropical disease',
                                    'published' => '2000-01-01T00:00:00Z',
                                    'image' => [
                                        'thumbnail' => [
                                            'alt' => '',
                                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                                            'source' => [
                                                'mediaType' => 'image/jpeg',
                                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                                'filename' => 'thumbnail.jpg',
                                            ],
                                            'size' => [
                                                'width' => 140,
                                                'height' => 140,
                                            ],
                                        ],
                                    ],
                                    'selectedCurator' => [
                                        'id' => 'pjha',
                                        'name' => [
                                            'preferred' => 'Prabhat Jha',
                                            'index' => 'Jha, Prabhat',
                                        ],
                                        'type' => 'senior-editor',
                                    ],
                                ],
                            ],
                            'impactStatement' => 'Chapter impact statement',
                        ],
                    ],
                    'impactStatement' => 'Podcast episode 1 impact statement',
                ],
                function ($test) {
                    $test->mockCollectionCall('tropical-disease', false);
                    $test->mockPersonCall('pjha', false);
                    $test->mockPersonCall('bcooper', false);
                    $test->mockBlogArticleCall('359325', false);
                    $test->mockSubjectCall('biophysics-structural-biology');
                },
            ],
            'minimum' => [
                new PodcastEpisode(
                    1,
                    'Podcast episode 1 title',
                    null,
                    $published,
                    null,
                    promise_for($banner),
                    $thumbnail,
                    [
                        new PodcastEpisodeSource(
                            'audio/mpeg',
                            'https://www.example.com/episode.mp3'
                        ),
                    ],
                    new EmptySequence(),
                    new ArraySequence([
                        new PodcastEpisodeChapter(
                            1,
                            'Chapter title',
                            0,
                            null,
                            new EmptySequence()),
                    ])
                ),
                [],
                [
                    'number' => 1,
                    'title' => 'Podcast episode 1 title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'image' => [
                        'thumbnail' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
                            ],
                        ],
                        'banner' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/banner.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/banner.jpg/full/full/0/default.jpg',
                                'filename' => 'banner.jpg',
                            ],
                            'size' => [
                                'width' => 1800,
                                'height' => 900,
                            ],
                        ],
                    ],
                    'sources' => [
                        [
                            'mediaType' => 'audio/mpeg',
                            'uri' => 'https://www.example.com/episode.mp3',
                        ],
                    ],
                    'chapters' => [
                        [
                            'number' => 1,
                            'title' => 'Chapter title',
                            'time' => 0,
                        ],
                    ],
                ],
            ],
            'complete snippet' => [
                new PodcastEpisode(1, 'Podcast episode 1 title', 'Podcast episode 1 impact statement', $published, $updated,
                    promise_for($banner), $thumbnail,
                    [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
                    new EmptySequence(), new ArraySequence([
                        new PodcastEpisodeChapter(1, 'Chapter title', 0, 'Chapter impact statement', new ArraySequence([
                            Builder::for(ArticlePoA::class)->sample('1'),
                        ])),
                    ])),
                ['snippet' => true, 'complete' => true, 'type' => true],
                [
                    'number' => 1,
                    'title' => 'Podcast episode 1 title',
                    'impactStatement' => 'Podcast episode 1 impact statement',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updated->format(ApiSdk::DATE_FORMAT),
                    'image' => [
                        'thumbnail' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
                            ],
                        ],
                    ],
                    'sources' => [
                        [
                            'mediaType' => 'audio/mpeg',
                            'uri' => 'https://www.example.com/episode.mp3',
                        ],
                    ],
                    'type' => 'podcast-episode',
                ],
                function (ApiTestCase $test) {
                    $test->mockPodcastEpisodeCall(1, true);
                },
            ],
            'minimum snippet' => [
                new PodcastEpisode(1, 'Podcast episode 1 title', null, $published, null, promise_for($banner), $thumbnail,
                    [new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3')],
                    new EmptySequence(), new ArraySequence([
                        new PodcastEpisodeChapter(1, 'Chapter title', 0, null, new EmptySequence()),
                    ])),
                ['snippet' => true],
                [
                    'number' => 1,
                    'title' => 'Podcast episode 1 title',
                    'published' => $published->format(ApiSdk::DATE_FORMAT),
                    'image' => [
                        'thumbnail' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
                            ],
                        ],
                    ],
                    'sources' => [
                        [
                            'mediaType' => 'audio/mpeg',
                            'uri' => 'https://www.example.com/episode.mp3',
                        ],
                    ],
                ],
                function (ApiTestCase $test) {
                    $test->mockPodcastEpisodeCall(1);
                },
            ],
        ];
    }
}
