<?php
namespace Mirin\Model;

use Dibi;
use Nette\Utils\Paginator;

class BlogEntryRepository
{

	const ITEMS_PER_PAGE = 5;

	/**
	 * @var Dibi\Connection
	 */
	private $db;

	public function __construct(Dibi\Connection $db)
	{
		$this->db = $db;
	}

	/**
	 * List of articles with comments and authors
	 * @param Paginator
	 * @return array
	 */
	public function getList(Paginator $paginator)
	{
		return $this->db->fetchAll("
			select article.title, article.titleUrl, article.posted, article.text,
				COUNT(comment.id) as commentsCount
			from article
			left join author on author.id = article.author_id
			left join comment on comment.article_id = article.id 
			where article.status = 'published'
			group by article.id
			order by article.posted desc
			%lmt %ofs", $paginator->getItemsPerPage(), $paginator->getOffset());
	}

	/**
	 * @return int
	 */
	public function getPublishedCount()
	{
		return (int)$this->db->fetchSingle("select count(*) from articles
			where status = 'published'");
	}

}