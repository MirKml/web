<?php
namespace Mirin\Presenters;

use Nette\Http;
use Nette\Utils;

class PaginatorFactory
{
	const PAGE_PARAMETER = "page";

	/**
	 * @var Http\IRequest
	 */
	private $request;

	public function __construct(Http\IRequest $request)
	{
		$this->request = $request;
	}

	/**
	 * @param int $itemsCount
	 * @param int $itemsPerPage
	 * @return Utils\Paginator
	 */
	public function getPaginator($itemsCount, $itemsPerPage)
	{
		$paginator = new Utils\Paginator();
		$paginator->setItemCount($itemsCount)
			->setItemsPerPage($itemsPerPage);
		if (($page = $this->request->getQuery(self::PAGE_PARAMETER))) {
			$paginator->setPage($page);
		} else {
			$paginator->setPage(1);
		}
		return $paginator;
	}

}