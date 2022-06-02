<?php

declare(strict_types=1);

namespace App\Application\Mail\CommandHandler;

use Throwable;
use Psr\Log\LoggerInterface;
use App\Application\Mail\Address;
use Contributte\Mailing\IMailBuilderFactory;
use App\Application\Mail\Command\SendMailCommand;
use SixtyEightPublishers\ArchitectureBundle\Command\CommandHandlerInterface;
use SixtyEightPublishers\TranslationBridge\PrefixedTranslatorFactoryInterface;

final class SendMailCommandHandler implements CommandHandlerInterface
{
	private string $templatesDirectory;

	private Address $fromAddress;

	private IMailBuilderFactory $mailBuilderFactory;

	private LoggerInterface $logger;

	private PrefixedTranslatorFactoryInterface $prefixedTranslatorFactory;

	/**
	 * @param string                                                                     $templatesDirectory
	 * @param \App\Application\Mail\Address                                              $fromAddress
	 * @param \Contributte\Mailing\IMailBuilderFactory                                   $mailBuilderFactory
	 * @param \Psr\Log\LoggerInterface                                                   $logger
	 * @param \SixtyEightPublishers\TranslationBridge\PrefixedTranslatorFactoryInterface $prefixedTranslatorFactory
	 */
	public function __construct(string $templatesDirectory, Address $fromAddress, IMailBuilderFactory $mailBuilderFactory, LoggerInterface $logger, PrefixedTranslatorFactoryInterface $prefixedTranslatorFactory)
	{
		$this->templatesDirectory = rtrim($templatesDirectory, DIRECTORY_SEPARATOR);
		$this->fromAddress = $fromAddress;
		$this->mailBuilderFactory = $mailBuilderFactory;
		$this->logger = $logger;
		$this->prefixedTranslatorFactory = $prefixedTranslatorFactory;
	}

	/**
	 * @param \App\Application\Mail\Command\SendMailCommand $command
	 *
	 * @return void
	 */
	public function __invoke(SendMailCommand $command): void
	{
		$mailBuilder = $this->mailBuilderFactory->create();
		$message = $command->message();
		$from = $message->from() ?? $this->fromAddress;

		$mailBuilder->setFrom($from->from(), $from->name());

		foreach ($message->to() as $address) {
			$mailBuilder->addTo($address->from(), $address->name());
		}

		foreach ($message->bcc() as $address) {
			$mailBuilder->addBcc($address->from(), $address->name());
		}

		foreach ($message->cc() as $address) {
			$mailBuilder->addCc($address->from(), $address->name());
		}

		foreach ($message->replyTo() as $address) {
			$mailBuilder->addReplyTo($address->from(), $address->name());
		}

		foreach ($message->attachments() as $attachment) {
			$mailBuilder->getMessage()->addAttachment($attachment->file(), $attachment->content(), $attachment->contentType());
		}

		$templateFile = $message->templateFile();

		if (0 === strncmp($templateFile, '~', 1)) {
			$templateFile = sprintf(
				'%s/%s',
				$this->templatesDirectory,
				substr($templateFile, 1)
			);
		}

		$mailBuilder->setTemplateFile($templateFile);
		$mailBuilder->getTemplate()->setTranslator($this->prefixedTranslatorFactory->create('mail'));
		$mailBuilder->setParameters($message->arguments());

		if (NULL !== $message->subject()) {
			$mailBuilder->setSubject($message->subject());
		}


		try {
			$mailBuilder->send();
		} catch (Throwable $e) {
			$this->logger->error((string) $e);
		}
	}
}
