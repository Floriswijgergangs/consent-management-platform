<?php

declare(strict_types=1);

namespace App\Infrastructure\Project\Doctrine\ReadModel;

use App\Domain\Project\Project;
use Doctrine\ORM\AbstractQuery;
use App\ReadModel\Project\ProjectView;
use Doctrine\ORM\EntityManagerInterface;
use App\ReadModel\Project\FindAllProjectsQuery;
use SixtyEightPublishers\ArchitectureBundle\ReadModel\View\ViewFactoryInterface;
use SixtyEightPublishers\ArchitectureBundle\ReadModel\Query\QueryHandlerInterface;
use SixtyEightPublishers\ArchitectureBundle\Infrastructure\Doctrine\ReadModel\DoctrineViewData;

final class FindAllProjectsQueryHandler implements QueryHandlerInterface
{
	private EntityManagerInterface $em;

	private ViewFactoryInterface $viewFactory;

	/**
	 * @param \Doctrine\ORM\EntityManagerInterface                                         $em
	 * @param \SixtyEightPublishers\ArchitectureBundle\ReadModel\View\ViewFactoryInterface $viewFactory
	 */
	public function __construct(EntityManagerInterface $em, ViewFactoryInterface $viewFactory)
	{
		$this->em = $em;
		$this->viewFactory = $viewFactory;
	}

	/**
	 * @param \App\ReadModel\Project\FindAllProjectsQuery $query
	 *
	 * @return \App\ReadModel\Project\ProjectView[]
	 */
	public function __invoke(FindAllProjectsQuery $query): array
	{
		$data = $this->em->createQueryBuilder()
			->select('p')
			->from(Project::class, 'p')
			->where('p.deletedAt IS NULL')
			->orderBy('p.name', 'ASC')
			->getQuery()
			->getResult(AbstractQuery::HYDRATE_ARRAY);

		return array_map(fn (array $row): ProjectView => $this->viewFactory->create(ProjectView::class, DoctrineViewData::create($row)), $data);
	}
}