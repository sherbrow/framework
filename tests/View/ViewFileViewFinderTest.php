<?php

use Mockery as m;

class ViewFinderTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testBasicViewFinding()
	{
		$finder = $this->getFinder();
		$finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo.php')->andReturn(true);

		$this->assertEquals(__DIR__.'/foo.php', $finder->find('foo'));
	}


	public function testCascadingFileLoading()
	{
		$finder = $this->getFinder();
		$finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo.php')->andReturn(false);
		$finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo.blade.php')->andReturn(true);

		$this->assertEquals(__DIR__.'/foo.blade.php', $finder->find('foo'));
	}


	public function testDirectoryCascadingFileLoading()
	{
		$finder = $this->getFinder();
		$finder->addLocation(__DIR__.'/nested');
		$finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo.php')->andReturn(false);
		$finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo.blade.php')->andReturn(false);
		$finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/nested/foo.php')->andReturn(true);

		$this->assertEquals(__DIR__.'/nested/foo.php', $finder->find('foo'));
	}


	public function testNamespacedBasicFileLoading()
	{
		$finder = $this->getFinder();
		$finder->addNamespace('foo', __DIR__.'/foo');
		$finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo/bar/baz.php')->andReturn(true);

		$this->assertEquals(__DIR__.'/foo/bar/baz.php', $finder->find('foo::bar.baz'));
	}


	public function testCascadingNamespacedFileLoading()
	{
		$finder = $this->getFinder();
		$finder->addNamespace('foo', __DIR__.'/foo');
		$finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo/bar/baz.php')->andReturn(false);
		$finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo/bar/baz.blade.php')->andReturn(true);

		$this->assertEquals(__DIR__.'/foo/bar/baz.blade.php', $finder->find('foo::bar.baz'));
	}


	public function testDirectoryCascadingNamespacedFileLoading()
	{
		$finder = $this->getFinder();
		$finder->addNamespace('foo', array(__DIR__.'/foo', __DIR__.'/bar'));
		$finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo/bar/baz.php')->andReturn(false);
		$finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo/bar/baz.blade.php')->andReturn(false);
		$finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/bar/bar/baz.php')->andReturn(true);

		$this->assertEquals(__DIR__.'/bar/bar/baz.php', $finder->find('foo::bar.baz'));
	}


	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionThrownWhenViewNotFound()
	{
		$finder = $this->getFinder();
		$finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo.php')->andReturn(false);
		$finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo.blade.php')->andReturn(false);

		$finder->find('foo');
	}


	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionThrownOnInvalidViewName()
	{
		$finder = $this->getFinder();
		$finder->find('name::');
	}


	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionThrownWhenNoHintPathIsRegistered()
	{
		$finder = $this->getFinder();
		$finder->find('name::foo');
	}


	protected function getFinder()
	{
		return new Illuminate\View\FileViewFinder(m::mock('Illuminate\Filesystem\Filesystem'), array(__DIR__));
	}

}