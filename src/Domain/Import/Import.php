<?php

declare(strict_types=1);

namespace App\Domain\Import;

use DateTimeImmutable;
use App\Domain\Import\ValueObject\Name;
use App\Domain\Import\ValueObject\Total;
use App\Domain\Import\Event\ImportFailed;
use App\Domain\Import\ValueObject\Author;
use App\Domain\Import\ValueObject\Output;
use App\Domain\Import\ValueObject\Status;
use App\Domain\Import\Event\ImportStarted;
use App\Domain\Import\ValueObject\ImportId;
use App\Domain\Import\Event\ImportCompleted;
use App\Domain\Import\Command\FailImportCommand;
use App\Domain\Import\Command\StartImportCommand;
use App\Domain\Import\Command\CompleteImportCommand;
use App\Domain\Import\Exception\InvalidStatusChangeException;
use SixtyEightPublishers\ArchitectureBundle\Domain\ValueObject\AggregateId;
use SixtyEightPublishers\ArchitectureBundle\Domain\Aggregate\AggregateRootTrait;
use SixtyEightPublishers\ArchitectureBundle\Domain\Aggregate\AggregateRootInterface;

final class Import implements AggregateRootInterface
{
	use AggregateRootTrait;

	private ImportId $id;

	private DateTimeImmutable $createdAt;

	private ?DateTimeImmutable $endedAt = NULL;

	private Name $name;

	private Status $status;

	private Author $author;

	private Total $imported;

	private Total $failed;

	private Output $output;

	/**
	 * @param \App\Domain\Import\Command\StartImportCommand $command
	 *
	 * @return static
	 */
	public static function create(StartImportCommand $command): self
	{
		$import = new self();

		$import->recordThat(ImportStarted::create(
			ImportId::fromString($command->id()),
			Name::fromValue($command->name()),
			Author::fromValue($command->author())
		));

		return $import;
	}

	/**
	 * {@inheritDoc}
	 */
	public function aggregateId(): AggregateId
	{
		return AggregateId::fromUuid($this->id->id());
	}

	/**
	 * @param \App\Domain\Import\Command\FailImportCommand $command
	 *
	 * @return void
	 */
	public function fail(FailImportCommand $command): void
	{
		$id = ImportId::fromString($command->id());

		if (!$this->id->equals($id) || !$this->status->is(Status::RUNNING)) {
			throw InvalidStatusChangeException::unableToFail($this->id);
		}

		$this->recordThat(ImportFailed::create(
			$id,
			Total::fromValue($command->imported()),
			Total::fromValue($command->failed()),
			Output::fromValue($command->output())
		));
	}

	/**
	 * @param \App\Domain\Import\Command\CompleteImportCommand $command
	 *
	 * @return void
	 */
	public function complete(CompleteImportCommand $command): void
	{
		$id = ImportId::fromString($command->id());

		if (!$this->id->equals($id) || !$this->status->is(Status::RUNNING)) {
			throw InvalidStatusChangeException::unableToComplete($this->id);
		}

		$this->recordThat(ImportCompleted::create(
			$id,
			Total::fromValue($command->imported()),
			Total::fromValue($command->failed()),
			Output::fromValue($command->output())
		));
	}

	/**
	 * @param \App\Domain\Import\Event\ImportStarted $event
	 *
	 * @return void
	 */
	protected function whenImportStarted(ImportStarted $event): void
	{
		$this->id = $event->id();
		$this->createdAt = $event->createdAt();
		$this->name = $event->name();
		$this->status = Status::running();
		$this->author = $event->author();
		$this->imported = Total::fromValue(0);
		$this->failed = Total::fromValue(0);
		$this->output = Output::fromValue('');
	}

	/**
	 * @param \App\Domain\Import\Event\ImportFailed $event
	 *
	 * @return void
	 */
	protected function whenImportFailed(ImportFailed $event): void
	{
		$this->endedAt = $event->createdAt();
		$this->status = Status::failed();
		$this->imported = $event->imported();
		$this->failed = $event->failed();
		$this->output = $event->output();
	}

	/**
	 * @param \App\Domain\Import\Event\ImportCompleted $event
	 *
	 * @return void
	 */
	protected function whenImportCompleted(ImportCompleted $event): void
	{
		$this->endedAt = $event->createdAt();
		$this->status = Status::completed();
		$this->imported = $event->imported();
		$this->failed = $event->failed();
		$this->output = $event->output();
	}
}