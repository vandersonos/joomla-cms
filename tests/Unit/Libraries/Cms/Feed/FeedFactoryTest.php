<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Feed;

use InvalidArgumentException;
use Joomla\CMS\Feed\FeedFactory;
use Joomla\CMS\Feed\FeedParser;
use Joomla\Tests\Unit\UnitTestCase;
use ReflectionClass;
use XMLReader;

/**
 * Test class for FeedFactory.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 */
class FeedFactoryTest extends UnitTestCase
{
	/**
	 * @var  FeedFactory
	 */
	private $feedFactory;

	/**
	 * Setup the tests.
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->feedFactory = new FeedFactory;
	}

	/**
	 * Method to tear down whatever was set up before the test.
	 */
	protected function tearDown()
	{
		unset($this->feedFactory);

		parent::tearDown();
	}

	/**
	 * Tests FeedFactory::getFeed().
	 */
	public function testGetFeed()
	{
		$this->markTestSkipped('We cant unit test FeedFactory::getFeed() at the moment, because it uses filesystem (XMLReader::open) and http service. It should be refactored and covered with integration tests.');
	}

	/**
	 *  Tests FeedFactory::getParsers().
	 */
	public function testGetDefaultParsers()
	{
		$defaultParsers = $this->feedFactory->getParsers();
		$this->assertCount(2, $defaultParsers);
		$this->assertArrayHasKey('rss', $defaultParsers);
		$this->assertArrayHasKey('feed', $defaultParsers);
	}

	/**
	 * Tests FeedFactory::registerParser()
	 */
	public function testRegisterParser()
	{
		$tagName = 'parser-mock';
		$parseMock = $this->createMock(FeedParser::class);
		$defaultParserCount = count($this->feedFactory->getParsers());

		$this->feedFactory->registerParser($tagName, get_class($parseMock));

		$feedParsers = $this->feedFactory->getParsers();
		$this->assertCount($defaultParserCount + 1, $feedParsers);
		$this->assertArrayHasKey($tagName, $feedParsers);
	}

	/**
	 * Tests FeedFactory::registerParser()
	 *
	 * @expectedException  InvalidArgumentException
	 */
	public function testRegisterParserWithInvalidClass()
	{
		$this->feedFactory->registerParser('does-not-exist', 'NotExistingClass');
	}

	/**
	 * Tests FeedFactory::registerParser()
	 *
	 * @expectedException  InvalidArgumentException
	 */
	public function testRegisterParserWithInvalidTag()
	{
		$parseMock = $this->createMock(FeedParser::class);
		$this->feedFactory->registerParser('42tag', get_class($parseMock));
	}

	/**
	 * Tests FeedFactory::_fetchFeedParser()
	 */
	public function testFetchFeedParser()
	{
		$tagName = 'parser-mock';
		$xmlReaderMock = $this->createMock(XMLReader::class);
		$parseMock = $this->createMock(FeedParser::class);
		$this->feedFactory->registerParser($tagName, get_class($parseMock));

		// Use reflection to test private method
		$reflectionClass = new ReflectionClass($this->feedFactory);
		$method = $reflectionClass->getMethod('_fetchFeedParser');
		$method->setAccessible(true);
		$parser = $method->invoke($this->feedFactory, $tagName, $xmlReaderMock);

		$this->assertInstanceOf(FeedParser::class, $parser);
		$this->assertSame(get_class($parseMock), get_class($parser));
	}

	/**
	 * Tests FeedFactory::_fetchFeedParser()
	 *
	 * @expectedException  LogicException
	 */
	public function testFetchFeedParserWithInvalidTag()
	{
		$xmlReaderMock = $this->createMock(XMLReader::class);

		// Use reflection to test private method
		$reflectionClass = new ReflectionClass($this->feedFactory);
		$method = $reflectionClass->getMethod('_fetchFeedParser');
		$method->setAccessible(true);
		$method->invoke($this->feedFactory, 'not-existing', $xmlReaderMock);
	}
}
