<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\InterviewsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Promise\CallbackPromise;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class Interviews implements Iterator, Collection
{
    use ArrayFromIterator;
    use SlicedIterator;

    private $count;
    private $interviews;
    private $descendingOrder = true;
    private $interviewsClient;
    private $denormalizer;

    public function __construct(InterviewsClient $interviewsClient, DenormalizerInterface $denormalizer)
    {
        $this->interviews = new ArrayObject();
        $this->interviewsClient = $interviewsClient;
        $this->denormalizer = $denormalizer;
    }

    public function __clone()
    {
        $this->resetIterator();
    }

    public function get(string $id) : PromiseInterface
    {
        if (isset($this->interviews[$id])) {
            return $this->interviews[$id];
        }

        return $this->interviews[$id] = $this->interviewsClient
            ->getInterview(
                ['Accept' => new MediaType(InterviewsClient::TYPE_INTERVIEW, 1)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), Interview::class);
            });
    }

    public function slice(int $offset, int $length = null) : Collection
    {
        if (null === $length) {
            return new PromiseCollection($this->all()
                ->then(function (Collection $collection) use ($offset) {
                    return $collection->slice($offset);
                })
            );
        }

        return new PromiseCollection($this->interviewsClient
            ->listInterviews(
                ['Accept' => new MediaType(InterviewsClient::TYPE_INTERVIEW_LIST, 1)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                $interviews = [];

                $fullPromise = new CallbackPromise(function () use ($result) {
                    $promises = [];
                    foreach ($result['items'] as $interview) {
                        $promises[$interview['id']] = $this->interviewsClient->getInterview(
                            ['Accept' => new MediaType(InterviewsClient::TYPE_INTERVIEW, 1)],
                            $interview['id']
                        );
                    }

                    return $promises;
                });

                foreach ($result['items'] as $interview) {
                    if (isset($this->interviews[$interview['id']])) {
                        $interviews[] = $this->interviews[$interview['id']]->wait();
                    } else {
                        $interview['interviewee']['cv'] = $fullPromise
                            ->then(function (array $promises) use ($interview) {
                                $fullInterview = $promises[$interview['id']]->wait();

                                if (empty($fullInterview['interviewee']['cv'])) {
                                    return [];
                                }

                                return $fullInterview['interviewee']['cv'];
                            });
                        $interview['content'] = $fullPromise
                            ->then(function (array $promises) use ($interview) {
                                return $promises[$interview['id']]->wait()['content'];
                            });

                        $interviews[] = $interview = $this->denormalizer->denormalize($interview, Interview::class);
                        $this->interviews[$interview->getId()] = promise_for($interview);
                    }
                }

                return new ArrayCollection($interviews);
            })
        );
    }

    public function reverse() : Collection
    {
        $clone = clone $this;

        $clone->descendingOrder = !$this->descendingOrder;

        return $clone;
    }

    public function count() : int
    {
        if (null === $this->count) {
            $this->slice(0, 1)->count();
        }

        return $this->count;
    }
}