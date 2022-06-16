<?php

declare(strict_types=1);

namespace App\Web\AdminModule\CookieModule\Presenter;

use App\Web\AdminModule\Presenter\AdminPresenter;
use App\Web\AdminModule\CookieModule\Control\ProviderList\ProviderListControl;
use App\Web\AdminModule\CookieModule\Control\ProviderList\ProviderListControlFactoryInterface;

final class ProvidersPresenter extends AdminPresenter
{
	private ProviderListControlFactoryInterface $providerListControlFactory;

	/**
	 * @param \App\Web\AdminModule\CookieModule\Control\ProviderList\ProviderListControlFactoryInterface $providerListControlFactory
	 */
	public function __construct(ProviderListControlFactoryInterface $providerListControlFactory)
	{
		parent::__construct();

		$this->providerListControlFactory = $providerListControlFactory;
	}

	/**
	 * @return void
	 */
	protected function startup(): void
	{
		parent::startup();

		$this->addBreadcrumbItem($this->getPrefixedTranslator()->translate('page_title'));
	}

	/**
	 * @return \App\Web\AdminModule\CookieModule\Control\ProviderList\ProviderListControl
	 */
	protected function createComponentList(): ProviderListControl
	{
		return $this->providerListControlFactory->create();
	}
}