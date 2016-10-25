<?php
namespace Mirin\AdminModule\Model;
use Dibi;
use Nette\Utils\Paginator;

class ArticleRepository
{

	const ITEMS_PER_PAGE = 20;
	const ITEMS_IN_RSS_LIST = 10;

	/**
	 * @var Dibi\Connection
	 */
	private $db;

	public function __construct(Dibi\Connection $db)
	{
		$this->db = $db;
	}

	/**
	 * @return int
	 */
	public function getAllCount()
	{
		return (int)$this->db->fetchSingle("select count(*) from article");
	}

	public function getItems(Paginator $paginator)
	{
		return $this->db->fetchAll("select *
			from article
			order by posted desc
			%lmt %ofs", $paginator->getItemsPerPage(), $paginator->getOffset());
	}
}