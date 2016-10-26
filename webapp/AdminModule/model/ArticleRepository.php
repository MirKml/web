<?php
namespace Mirin\AdminModule\Model;
use Dibi;
use Nette\Utils;

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

	/**
	 * @param Utils\Paginator $paginator
	 * @return Dibi\Row[]
	 */
	public function getItems(Utils\Paginator $paginator)
	{
		return $this->db->fetchAll("select *
			from article
			order by posted desc
			%lmt %ofs", $paginator->getItemsPerPage(), $paginator->getOffset());
	}

	/**
	 * @param $id
	 * @return Dibi\Row|false
	 */
	public function getById($id)
	{
		return $this->db->fetch("select * from article where id = %i", $id);
	}

	public function update($id, Utils\ArrayHash $values)
	{
		$this->db->query("update article set", [
			"title" => $values->title,
			"author_id" => $values->author,
			"posted" => new \DateTime($values->posted),
			"titleUrl" => Utils\Strings::webalize($values->title),
			"text" => $values->mainText,
			"status" => $values->status
		], "where id = %i", $id);
	}
}