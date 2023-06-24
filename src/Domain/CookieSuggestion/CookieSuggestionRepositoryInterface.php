<?php

declare(strict_types=1);

namespace App\Domain\CookieSuggestion;

use App\Domain\CookieSuggestion\ValueObject\CookieSuggestionId;
use App\Domain\CookieSuggestion\Exception\CookieSuggestionNotFoundException;

interface CookieSuggestionRepositoryInterface
{
	public function save(CookieSuggestion $cookieSuggestion): void;

	/**
	 * @throws CookieSuggestionNotFoundException
	 */
	public function get(CookieSuggestionId $id): CookieSuggestion;
}
