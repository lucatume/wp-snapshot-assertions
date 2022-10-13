<?php

namespace tad\WP\Snapshots;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use tad\WP\Snapshots\WPHtmlOutputDriver as Driver;

class WPHtmlOutputDriverTest extends TestCase {

	public $currentUrl = 'http://example.com';

	public $examplesUrl = 'http://example.com';

	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @test
	 * it should be instantiable
	 */
	public function it_should_be_instantiable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf(Driver::class, $sut);
	}

	/**
	 * @return Driver
	 */
	private function make_instance() {
		return new Driver($this->currentUrl);
	}

	/**
	 * It should match two identical HTML documents
	 *
	 * @test
	 */
	public function should_match_two_identical_html_documents() {
		$file = 'html-1';
		$one = $two = $this->getSourceFileContents($file);

		$driver = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	protected function getSourceFileContents(string $file): string {
		return file_get_contents(dataDir('snapshots/' . $file . '.php'));
	}

	/**
	 * It should match two identical docs differing by nonce
	 *
	 * @test
	 */
	public function should_match_two_identical_docs_differing_by_nonce() {
		$file = 'html-2';
		$one = $two = $this->getSourceFileContents($file);
		$driver = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	/**
	 * It should match two identical docs differing by URLs
	 *
	 * @test
	 */
	public function should_match_two_identical_docs_differing_by_urls() {
		$file = 'html-3';
		$one = $two = $this->getSourceFileContents($file);

		$this->currentUrl = 'http://www.theaveragedev.com';
		$two = $this->replaceExampleUrlIn($two);
		$driver = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	protected function replaceExampleUrlIn(string $input): string {
		return str_replace($this->examplesUrl, $this->currentUrl, $input);
	}

	/**
	 * It should match two identical docs differing in URL and scheme
	 *
	 * @test
	 */
	public function should_match_two_identical_docs_differing_in_url_and_scheme() {
		$file = 'html-3';
		$one = $two = $this->getSourceFileContents($file);

		$this->currentUrl = 'https://www.theaveragedev.com';
		$two = $this->replaceExampleUrlIn($two);
		$driver = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	/**
	 * It should match two identical docs differing in URL, scheme and port
	 *
	 * @test
	 */
	public function should_match_two_identical_docs_differing_in_url_scheme_and_port() {
		$file = 'html-3';
		$one = $two = $this->getSourceFileContents($file);

		$this->currentUrl = 'https://www.theaveragedev.com:8080';
		$two = $this->replaceExampleUrlIn($two);
		$driver = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	/**
	 * It should match two identical docs differing in url, scheme, port and path
	 *
	 * @test
	 */
	public function should_match_two_identical_docs_differing_in_url_scheme_port_and_path() {
		$file = 'html-3';
		$one = $two = $this->getSourceFileContents($file);

		$this->currentUrl = 'https://www.theaveragedev.com:8080/some/path';
		$two = $this->replaceExampleUrlIn($two);
		$driver = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	/**
	 * It should allow comparing HTML fragments
	 *
	 * @test
	 */
	public function should_allow_comparing_html_fragments() {
		$file = 'html-4';
		$one = $two = $this->getSourceFileContents($file);

		$this->currentUrl = 'https://www.theaveragedev.com:8080/some/path';
		$two = $this->replaceExampleUrlIn($two);
		$driver = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	/**
	 * It should allow defining the snapshot URL to only replace that
	 *
	 * @test
	 */
	public function should_allow_defining_the_snapshot_url_to_only_replace_that() {
		$template = $this->getSourceFileContents('html-5');

		$currentUrl = 'https://www.theaveragedev.com:8080/some/path';
		$snapshotUrl = 'http://example.com';
		$expected = $this->replaceUrlInTemplate($snapshotUrl, $template);
		$actual = $this->replaceUrlInTemplate($currentUrl, $template);
		$driver = new Driver($currentUrl, $snapshotUrl);

		$driver->match($expected, $driver->evalCode($actual));
	}

	protected function replaceUrlInTemplate($replacementUrl, $template): string {
		return str_replace('{{url}}', $replacementUrl, $template);
	}

	/**
	 * It should allow setting tolerable differences
	 *
	 * @test
	 */
	public function should_allow_setting_tolerable_differences() {
		$template = $this->getSourceFileContents('html-6');

		$driver = new Driver();

		$actual = str_replace(['{{one}}', '{{two}}'], ['23', '89'], $template);
		$expected = str_replace(['{{one}}', '{{two}}'], ['foo', 'bar'], $template);

		$driver->setTolerableDifferences(['23', '89']);

		$driver->match($expected, $driver->evalCode($actual));
	}

	/**
	 * It should allow setting prefixes for tolerable differences
	 *
	 * @test
	 */
	public function should_allow_setting_prefixes_for_tolerable_differences() {
		$template = $this->getSourceFileContents('html-7');

		$driver = new Driver();

		$actual = str_replace(['{{one}}', '{{two}}'], ['23', '89'], $template);
		$expected = str_replace(['{{one}}', '{{two}}'], ['foo', 'bar'], $template);

		$driver->setTolerableDifferences(['23', '89']);
		$driver->setTolerableDifferencesPostfixes(['prefix-', 'another_prefix-']);

		$driver->match($expected, $driver->evalCode($actual));
	}

	/**
	 * It should allow setting postfixes for tolerable differences
	 *
	 * @test
	 */
	public function should_allow_setting_postfixes_for_tolerable_differences() {
		$template = $this->getSourceFileContents('html-8');

		$driver = new Driver();

		$actual = str_replace(['{{one}}', '{{two}}'], ['23', '89'], $template);
		$expected = str_replace(['{{one}}', '{{two}}'], ['foo', 'bar'], $template);

		$driver->setTolerableDifferences(['23', '89']);
		$driver->setTolerableDifferencesPostfixes(['-postfix', '-another_postfix']);

		$driver->match($expected, $driver->evalCode($actual));
	}

	/**
	 * It should allow setting prefixes and postfixes for tolerable differences
	 *
	 * @test
	 */
	public function should_allow_setting_prefixes_and_postfixes_for_tolerable_differences() {
		$template = $this->getSourceFileContents('html-9');

		$driver = new Driver();

		$actual = str_replace(['{{one}}', '{{two}}'], ['23', '89'], $template);
		$expected = str_replace(['{{one}}', '{{two}}'], ['foo', 'bar'], $template);

		$driver->setTolerableDifferences(['23', '89']);
		$driver->setTolerableDifferencesPostfixes(['prefix-', 'another_prefix-']);
		$driver->setTolerableDifferencesPostfixes(['-postfix', '-another_postfix']);

		$driver->match($expected, $driver->evalCode($actual));
	}

	/**
	 * It should be able to match a complex URL
	 *
	 * @test
	 */
	public function should_be_able_to_match_a_complex_url() {
		$file = 'html-3';
		$one = $two = $this->getSourceFileContents($file);

		$this->currentUrl = 'http://test.foo.bar';
		$two = $this->replaceExampleUrlIn($two);
		$driver = $this->make_instance();

		$driver->match($one, $driver->evalCode($two));
	}

	/**
	 * It should be able to match a time dependent attribute
	 *
	 * @test
	 */
	public function should_be_able_to_match_a_time_dependent_attribute() {
		$template = $this->getSourceFileContents('html-10');

		$driver = new Driver();

		$actual = str_replace(['{{one}}', '{{two}}'], ['23', '89'], $template);
		$expected = str_replace(['{{one}}', '{{two}}'], ['foo', 'bar'], $template);

		$driver->setTimeDependentAttributes(['data-id']);

		$driver->match($expected, $driver->evalCode($actual));
	}

	/**
	 * It should allow setting a context for time dependent data attributes
	 *
	 * @test
	 */
	public function should_allow_setting_a_context_for_time_dependent_data_attributes() {
		$template = $this->getSourceFileContents('html-11');

		$driver = new Driver();

		$actual = str_replace(['{{one}}', '{{two}}'], ['23', '89'], $template);
		$expected = str_replace(['{{one}}', '{{two}}'], ['foo', 'bar'], $template);

		$driver->setTimeDependentAttributes(['data-id'],'.container');

		$driver->match($expected, $driver->evalCode($actual));
	}

	/**
	 * It should fail if context set for time dependent attributes does not cover time dependent attribute
	 *
	 * @test
	 */
	public function should_fail_if_context_set_for_time_dependent_attributes_does_not_cover_time_dependent_attribute() {
		$template = $this->getSourceFileContents('html-12');

		$driver = new Driver();

		$actual = str_replace(['{{one}}', '{{two}}'], ['23', '89'], $template);
		$expected = str_replace(['{{one}}', '{{two}}'], ['foo', 'bar'], $template);

		$driver->setTimeDependentAttributes(['data-id'],'.container__one');

		$this->expectException(AssertionFailedError::class);

		$driver->match($expected, $driver->evalCode($actual));
	}
}
