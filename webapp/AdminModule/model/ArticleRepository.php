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

	/**
	 * Update the article in db
	 * @param int $id ID of updated article
	 * @param Utils\ArrayHash $articleData
	 */
	public function update($id, Utils\ArrayHash $articleData)
	{
		$this->db->query("update article set", [
			"title" => $articleData->title,
			"author_id" => $articleData->author,
			"posted" => new \DateTime($articleData->posted),
			"titleUrl" => Utils\Strings::webalize($articleData->title),
			"text" => $articleData->mainText,
			"status" => $articleData->status
		], "where id = %i", $id);

		if (isset($articleData->categories)) {
			$this->db->query("delete from categoryarticle where article_id = %i", $id);
			foreach ($articleData->categories as $categoryId) {
				$this->db->query("insert into categoryarticle", [
					"article_id" => $id,
					"category_id" => $categoryId
				]);
			}
		}
	}

	/**
	 * Insert new article into db
	 * @param Utils\ArrayHash $articleData
	 * @return int ID of new db record
	 */
	public function insert(Utils\ArrayHash $articleData)
	{
		$this->db->query("insert into article", [
			"title" => $articleData->title,
			"author_id" => $articleData->author,
			"posted" => new \DateTime($articleData->posted),
			"titleUrl" => Utils\Strings::webalize($articleData->title),
			"text" => $articleData->mainText,
			"status" => $articleData->status,
			"plainText" => ""
		]);
		return $this->db->getInsertId();
	}
}