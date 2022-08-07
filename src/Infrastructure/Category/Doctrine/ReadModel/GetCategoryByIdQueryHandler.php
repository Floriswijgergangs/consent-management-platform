<?php

declare(strict_types=1);

namespace App\Infrastructure\Category\Doctrine\ReadModel;

use Doctrine\ORM\AbstractQuery;
use App\Domain\Category\Category;
use App\ReadModel\Category\CategoryView;
use Doctrine\ORM\EntityManagerInterface;
use App\ReadModel\Category\GetCategoryByIdQuery;
use SixtyEightPublishers\ArchitectureBundle\ReadModel\View\ViewFactoryInterface;
use SixtyEightPublishers\ArchitectureBundle\ReadModel\Query\QueryHandlerInterface;
use SixtyEightPublishers\ArchitectureBundle\Infrastructure\Doctrine\ReadModel\DoctrineViewData;

final class GetCategoryByIdQueryHandler implements QueryHandlerInterface
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
	 * @param \App\ReadModel\Category\GetCategoryByIdQuery $query
	 *
	 * @return \App\ReadModel\Category\CategoryView|NULL
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function __invoke(GetCategoryByIdQuery $query): ?CategoryView
	{
		$data = $this->em->createQueryBuilder()
			->select('c, ct')
			->from(Category::class, 'c')
			->leftJoin('c.translations', 'ct')
			->where('c.id = :id')
			->andWhere('c.deletedAt IS NULL')
			->setParameter('id', $query->id())
			->getQuery()
			->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

		return NULL !== $data ? $this->viewFactory->create(CategoryView::class, DoctrineViewData::create($data)) : NULL;
	}
}
