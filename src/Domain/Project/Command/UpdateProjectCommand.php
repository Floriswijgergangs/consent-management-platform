<?php

declare(strict_types=1);

namespace App\Domain\Project\Command;

use SixtyEightPublishers\ArchitectureBundle\Command\AbstractCommand;

final class UpdateProjectCommand extends AbstractCommand
{
	/**
	 * @param string $projectId
	 *
	 * @return static
	 */
	public static function create(string $projectId): self
	{
		return self::fromParameters([
			'project_id' => $projectId,
		]);
	}

	/**
	 * @return string
	 */
	public function projectId(): string
	{
		return $this->getParam('project_id');
	}

	/**
	 * @return string|NULL
	 */
	public function name(): ?string
	{
		return $this->getParam('name');
	}

	/**
	 * @return string|NULL
	 */
	public function code(): ?string
	{
		return $this->getParam('code');
	}

	/**
	 * @return string|NULL
	 */
	public function description(): ?string
	{
		return $this->getParam('description');
	}

	/**
	 * @return string|NULL
	 */
	public function color(): ?string
	{
		return $this->getParam('color');
	}

	/**
	 * @return bool|NULL
	 */
	public function active(): ?bool
	{
		return $this->getParam('active');
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function withName(string $name): self
	{
		return $this->withParam('name', $name);
	}

	/**
	 * @param string $code
	 *
	 * @return $this
	 */
	public function withCode(string $code): self
	{
		return $this->withParam('code', $code);
	}

	/**
	 * @param string $color
	 *
	 * @return $this
	 */
	public function withColor(string $color): self
	{
		return $this->withParam('color', $color);
	}

	/**
	 * @param string $description
	 *
	 * @return $this
	 */
	public function withDescription(string $description): self
	{
		return $this->withParam('description', $description);
	}

	/**
	 * @param bool $active
	 *
	 * @return $this
	 */
	public function withActive(bool $active): self
	{
		return $this->withParam('active', $active);
	}
}
