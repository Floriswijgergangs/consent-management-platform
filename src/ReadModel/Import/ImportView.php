<?php

declare(strict_types=1);

namespace App\ReadModel\Import;

use DateTimeImmutable;
use DateTimeInterface;
use App\Domain\Import\ValueObject\Name;
use App\Domain\Import\ValueObject\Total;
use App\Domain\Import\ValueObject\Output;
use App\Domain\Import\ValueObject\Status;
use App\Domain\Import\ValueObject\ImportId;
use SixtyEightPublishers\UserBundle\Domain\ValueObject\UserId;
use SixtyEightPublishers\ArchitectureBundle\ReadModel\View\AbstractView;

final class ImportView extends AbstractView
{
	public ImportId $id;

	public ?UserId $authorId = NULL;

	public DateTimeImmutable $createdAt;

	public ?DateTimeImmutable $endedAt = NULL;

	public Name $name;

	public Status $status;

	public Total $imported;

	public Total $failed;

	public Total $warned;

	public Output $output;

	/**
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return [
			'id' => $this->id->toString(),
			'author_id' => NULL !== $this->authorId ? $this->authorId->toString() : NULL,
			'createdAt' => $this->createdAt->format(DateTimeInterface::ATOM),
			'endedAt' => NULL !== $this->endedAt ? $this->endedAt->format(DateTimeInterface::ATOM) : NULL,
			'name' => $this->name->value(),
			'status' => $this->status->value(),
			'imported' => $this->imported->value(),
			'failed' => $this->failed->value(),
			'warned' => $this->warned->value(),
			'output' => $this->output->value(),
		];
	}
}
