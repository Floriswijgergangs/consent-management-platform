<?php

declare(strict_types=1);

namespace App\Web\Ui;

use Nette\Application\Request;
use Nette\Application\Responses\ForwardResponse;
use SixtyEightPublishers\NotificationBundle\UI\TNotifier;
use SixtyEightPublishers\TranslationBridge\TranslatorAwareTrait;
use SixtyEightPublishers\TranslationBridge\TranslatorAwareInterface;
use SixtyEightPublishers\SmartNetteComponent\UI\Presenter as SmartPresenter;
use SixtyEightPublishers\TranslationBridge\Localization\TranslatorLocalizerInterface;

abstract class Presenter extends SmartPresenter implements TranslatorAwareInterface
{
	use TNotifier;
	use TranslatorAwareTrait;
	use RedrawControlTrait;

	private TranslatorLocalizerInterface $translatorLocalizer;

	/**
	 * @internal
	 *
	 * @param \SixtyEightPublishers\TranslationBridge\Localization\TranslatorLocalizerInterface $translatorLocalizer
	 *
	 * @return void
	 */
	public function injectBaseDependencies(TranslatorLocalizerInterface $translatorLocalizer): void
	{
		$this->translatorLocalizer = $translatorLocalizer;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function beforeRender(): void
	{
		$this->template->setTranslator($this->getPrefixedTranslator());

		$this->template->locale = $this->translatorLocalizer->getLocale();
		$this->template->lang = current(explode('_', $this->translatorLocalizer->getLocale()));
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function restoreRequest($key): void
	{
		$session = $this->getSession('Nette.Application/requests');

		if (!isset($session[$key]) || ($session[$key][0] !== NULL && (string) $session[$key][0] !== (string) $this->getUser()->getId())) {
			return;
		}

		/** @var \Nette\Application\Request $request */
		$request = clone $session[$key][1];

		unset($session[$key]);

		$request->setFlag(Request::RESTORED, TRUE);

		$params = $request->getParameters();
		$params[self::FLASH_KEY] = $this->getFlashKey();

		$request->setParameters($params);
		$this->sendResponse(new ForwardResponse($request));
	}

	/**
	 * @return string|NULL
	 */
	private function getFlashKey(): ?string
	{
		$flashKey = $this->getParameter(self::FLASH_KEY);

		return is_string($flashKey) && $flashKey !== ''
			? $flashKey
			: NULL;
	}
}
