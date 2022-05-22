<?php

declare(strict_types=1);

namespace App\Web\AdminModule\ProjectModule\Presenter;

use Nette\Application\UI\Component;
use App\ReadModel\Project\ProjectView;
use Contributte\MenuControl\MenuContainer;
use Contributte\MenuControl\UI\MenuComponent;
use Nette\Application\ForbiddenRequestException;
use App\Web\AdminModule\Presenter\AdminPresenter;
use App\ReadModel\Project\GetUsersProjectByCodeQuery;
use SixtyEightPublishers\SmartNetteComponent\Annotation\Layout;
use SixtyEightPublishers\ArchitectureBundle\Bus\QueryBusInterface;

/**
 * @Layout(path="templates/SelectedProject.@layout.latte")
 */
abstract class SelectedProjectPresenter extends AdminPresenter
{
	private const MENU_NAME_SIDEBAR_PROJECT = 'sidebar_project';

	/** @persistent */
	public string $project = '';
	
	protected QueryBusInterface $queryBus;

	protected ProjectView $projectView;

	private MenuContainer $menuContainer;

	/**
	 * @param \SixtyEightPublishers\ArchitectureBundle\Bus\QueryBusInterface $queryBus
	 * @param \Contributte\MenuControl\MenuContainer                         $menuContainer
	 *
	 * @return void
	 */
	public function injectProjectDependencies(QueryBusInterface $queryBus, MenuContainer $menuContainer): void
	{
		$this->queryBus = $queryBus;
		$this->menuContainer = $menuContainer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkRequirements($element): void
	{
		parent::checkRequirements($element);

		if (empty($this->project)) {
			throw new ForbiddenRequestException('Project is not selected.');
		}

		$this->refreshProjectView($this->project);
	}

	/**
	 * @param string $code
	 *
	 * @return void
	 * @throws \Nette\Application\ForbiddenRequestException
	 */
	protected function refreshProjectView(string $code): void
	{
		$this->project = $code;
		$projectView = $this->queryBus->dispatch(GetUsersProjectByCodeQuery::create($code, $this->getIdentity()->id()->toString()));

		if (!$projectView instanceof ProjectView) {
			throw new ForbiddenRequestException('Project not exists or not associated with the current user.');
		}

		$this->projectView = $projectView;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function beforeRender(): void
	{
		parent::beforeRender();

		$this->template->projectView = $this->projectView;
	}

	/**
	 * @return \Contributte\MenuControl\UI\MenuComponent
	 */
	protected function createComponentSidebarProjectMenu(): MenuComponent
	{
		$items = $this->menuContainer->getMenu(self::MENU_NAME_SIDEBAR_PROJECT)->getItems();

		($setupItems = function (array $items) use (&$setupItems) {
			foreach ($items as $item) {
				$item->setAction($item->getAction(), [
					'project' => $this->project,
				]);

				$setupItems($item->getItems());
			}
		})($items);

		$control = new MenuComponent($this->menuContainer, self::MENU_NAME_SIDEBAR_PROJECT);

		$control->onAnchor[] = function (Component $component) {
			$component->template->customBreadcrumbItems = $this->customBreadcrumbItems;
		};

		return $control;
	}
}