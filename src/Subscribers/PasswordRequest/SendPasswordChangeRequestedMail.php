<?php

declare(strict_types=1);

namespace App\Subscribers\PasswordRequest;

use App\Application\Mail\Address;
use App\Application\Mail\Message;
use App\Application\Mail\Command\SendMailCommand;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use SixtyEightPublishers\ArchitectureBundle\Bus\CommandBusInterface;
use SixtyEightPublishers\ArchitectureBundle\Event\EventHandlerInterface;
use SixtyEightPublishers\ForgotPasswordBundle\Domain\Event\PasswordChangeRequested;

final class SendPasswordChangeRequestedMail implements EventHandlerInterface
{
	private CommandBusInterface $commandBus;

	/**
	 * @param \SixtyEightPublishers\ArchitectureBundle\Bus\CommandBusInterface $commandBus
	 */
	public function __construct(CommandBusInterface $commandBus)
	{
		$this->commandBus = $commandBus;
	}

	/**
	 * @param \SixtyEightPublishers\ForgotPasswordBundle\Domain\Event\PasswordChangeRequested $event
	 *
	 * @return void
	 */
	public function __invoke(PasswordChangeRequested $event): void
	{
		$message = Message::create('~passwordChangeRequested.latte')
			->withTo(Address::create($event->emailAddress()->value()))
			->withArguments([
				'emailAddress' => $event->emailAddress()->value(),
				'passwordRequestId' => $event->passwordRequestId()->toString(),
				'expireAt' => $event->expiredAt(),
			]);

		$this->commandBus->dispatch(SendMailCommand::create($message), [
			new DispatchAfterCurrentBusStamp(),
		]);
	}
}
