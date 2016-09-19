<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Subject;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class BlogArticleNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use SubjectsAware;

    public function denormalize($data, $class, $format = null, array $context = []) : BlogArticle
    {
        $data['content'] = new PromiseCollection(promise_for($data['content'])
            ->then(function (array $blocks) use ($format, $context) {
                return array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                }, $blocks);
            }));

        $data['subjects'] = !empty($data['subjects']) ? $this->getSubjects($data['subjects']) : null;

        return new BlogArticle(
            $data['id'],
            $data['title'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            $data['impactStatement'] ?? null,
            $data['content'],
            $data['subjects']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return BlogArticle::class === $type;
    }

    /**
     * @param BlogArticle $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format(DATE_ATOM),
        ];

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if ($object->hasSubjects()) {
            $data['subjects'] = $object->getSubjects()->map(function (Subject $subject) {
                return $subject->getId();
            });
        }

        if (empty($context['snippet'])) {
            $data['content'] = $object->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            });
        }

        return all($data)->wait();
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof BlogArticle;
    }
}