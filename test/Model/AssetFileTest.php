<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Asset;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\File;
use PHPUnit_Framework_TestCase;

final class AssetFileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_an_asset()
    {
        $file = new AssetFile(null, null, null, null, new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertInstanceOf(Asset::class, $file);
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new AssetFile('10.1000/182', null, null, null, new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));
        $withOut = new AssetFile(null, null, null, null, new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new AssetFile(null, 'id', null, null, new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));
        $withOut = new AssetFile(null, null, null, null, new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_label()
    {
        $with = new AssetFile(null, null, 'label', null, new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));
        $withOut = new AssetFile(null, null, null, null, new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertSame('label', $with->getLabel());
        $this->assertNull($withOut->getLabel());
    }

    /**
     * @test
     */
    public function it_may_have_a_title()
    {
        $with = new AssetFile(null, null, null, 'title', new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));
        $withOut = new AssetFile(null, null, null, null, new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertSame('title', $with->getTitle());
        $this->assertNull($withOut->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_caption()
    {
        $caption = new ArraySequence([new Paragraph('caption')]);
        $with = new AssetFile(null, null, null, null, $caption, new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));
        $withOut = new AssetFile(null, null, null, null, new EmptySequence(), new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertEquals($caption, $with->getCaption());
        $this->assertEmpty($withOut->getCaption());
    }

    /**
     * @test
     */
    public function it_has_a_file()
    {
        $file = new AssetFile(null, null, null, null, new EmptySequence(), $theFile = new File('image/jpeg', 'http://www.example.com/image.jpg', 'image.jpg'));

        $this->assertEquals($theFile, $file->getFile());
    }
}
