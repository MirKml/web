<?php
namespace Mirin\Model;

use Dibi;
use Nette\Utils\Paginator;

class BlogArticleRepository
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
	 * @param Paginator $paginator
	 * @return array
	 */
	public function getList(Paginator $paginator)
	{
		$articles = [];
		foreach ($this->db->query("
			select article.id, article.title, article.titleUrl, article.posted, article.text,
				article.format,
				author.name as authorName,
				COUNT(comment.id) as commentsCount
			from article
			inner join author on author.id = article.author_id
			left join comment on comment.article_id = article.id 
			where article.status = 'published'
			group by article.id
			order by article.posted desc
			%lmt %ofs", $paginator->getItemsPerPage(), $paginator->getOffset())
				 as $articleRow) {

			$articles[] = new BlogArticle($articleRow);
		}

		return $articles;
	}

	/**
	 * @param string $slug
	 * @return BlogArticle|void
	 */
	public function getBySlug($slug)
	{
		$articleRow = $this->db->fetch("select article.id, title, posted, text, format,
			author.name as authorName
			from article
			inner join author on author.id = article.author_id
			where titleUrl = %s", $slug, "
				and status = 'published'");
		if (!$articleRow) return;
		return new BlogArticle($articleRow);
	}

	/**
	 * @return int
	 */
	public function getPublishedCount()
	{
		return (int)$this->db->fetchSingle("select count(*) from article
			where status = 'published'");
	}

	/**
	 * @param BlogArticle $current
	 * @return BlogArticle|void
	 */
	public function getPrevious(BlogArticle $current)
	{
		$articleRow = $this->db->fetch("select id, title, posted, titleUrl, status
			from article
			where posted < %t", $current->posted, "
				and status = 'published'
			order by posted desc
			limit 1");
		if (!$articleRow) return;
		return new BlogArticle($articleRow);
	}

	/**
	 * @param BlogArticle $current
	 * @return BlogArticle|void
	 */
	public function getNext(BlogArticle $current)
	{
		$articleRow = $this->db->fetch("select id, title, posted, titleUrl, status
			from article
			where posted > %t", $current->posted, "
				and status = 'published'
			order by posted asc
			limit 1");
		if (!$articleRow) return;
		return new BlogArticle($articleRow);
	}
}