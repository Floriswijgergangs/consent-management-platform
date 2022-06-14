<?php

declare(strict_types=1);

namespace App\Domain\Cookie;

use App\Domain\Shared\ValueObject\Locale;
use App\Domain\Cookie\ValueObject\Purpose;

final class CookieTranslation
{
	# autogenerated
	private string $id;

	private Cookie $cookie;

	private Locale $locale;

	private Purpose $purpose;

	private function __construct()
	{
	}

	/**
	 * @param \App\Domain\Cookie\Cookie              $cookie
	 * @param \App\Domain\Shared\ValueObject\Locale  $locale
	 * @param \App\Domain\Cookie\ValueObject\Purpose $purpose
	 *
	 * @return static
	 */
	public static function create(Cookie $cookie, Locale $locale, Purpose $purpose): self
	{
		$cookieTranslation = new self();
		$cookieTranslation->cookie = $cookie;
		$cookieTranslation->locale = $locale;
		$cookieTranslation->purpose = $purpose;

		return $cookieTranslation;
	}

	/**
	 * @return \App\Domain\Cookie\Cookie
	 */
	public function cookie(): Cookie
	{
		return $this->cookie;
	}

	/**
	 * @return \App\Domain\Shared\ValueObject\Locale
	 */
	public function locale(): Locale
	{
		return $this->locale;
	}

	/**
	 * @return \App\Domain\Cookie\ValueObject\Purpose
	 */
	public function purpose(): Purpose
	{
		return $this->purpose;
	}

	/**
	 * @param \App\Domain\Cookie\ValueObject\Purpose $purpose
	 *
	 * @return void
	 */
	public function setPurpose(Purpose $purpose): void
	{
		$this->purpose = $purpose;
	}
}
