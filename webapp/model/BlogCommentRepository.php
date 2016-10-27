<?php
namespace Mirin\Model;
use Dibi;
use Nette\Utils\Paginator;

class BlogCommentRepository
{
	/**
	 * @var Dibi\Connection
	 */
	private $db;

	public function __construct(Dibi\Connection $db)
	{
		$this->db = $db;
	}

	/**
	 * @param BlogArticle $article
	 * @return array
	 */
	public function getByArticle(BlogArticle $article)
	{
		foreach ($this->db->fetchAll("select *
			from comment
			where article_id = %i", $article->id) as $commentRow) {

			$comments[] = new BlogComment($commentRow);
		};
		return isset($comments) ? $comments : [];
	}

	/**
	 * @param array $comment comment from the user input - comment form
	 *  message is without any filtering, it's original text from the user comment form
	 *  indices corresponds the blog comment entity properties
	 * @return BlogComment
	 */
	public static function createPreviewComment(array $comment)
	{
		$message = $comment["message"];

		// filter the comment message from user into format suitable
		// for storing in the database
		// makes html special chars conversion for all text in the <pre> tag for a whole
		// comment text.
		$pattern = "/<pre>((\\n|.)*)<\/pre>/U";
		$message = preg_replace_callback($pattern, function ($matches) {
			return "<pre>" . htmlspecialchars($matches[1]) . "</pre>";
		}, $message);

		$message = preg_replace("/\s+(<pre)/", "\\1", $message);
		$message = preg_replace("/(pre>)\s*/", "\\1", $message);
		$comment["message"] = strip_tags($message, "<a><pre>");

		return new BlogComment(new Dibi\Row($comment));
	}

	/**
	 * Gets count of comments for the article
	 * @param BlogArticle $article
	 */
	public function getCountForArticle(BlogArticle $article)
	{
		return $this->db->fetchSingle("select count(*) from comment where article_id = %i", $article->id);
	}

	public function insert(BlogComment $comment)
	{
		$this->db->query("insert into comment", [
			"article_id" => $comment->article_id,
			"name" => $comment->name,
			"email" => $comment->email,
			"message" => $comment->message,
			"www" => $comment->www,
			"posted" => $comment->posted
		]);
	}

	/**
	 * @return int
	 */
	public function getAllCount()
	{
		return (int)$this->db->fetchSingle("select count(*) from comment");
	}

	/**
	 * Get list of comments for particular page
	 * It's used mostly for administration
	 * @param Paginator $paginator
	 * @return array
	 */
	public function getForPage(Paginator $paginator)
	{
		foreach ($this->db->fetchAll("select comment.*,
				article.title as articleTitle
			from comment
			inner join article on article.id = comment.article_id
			order by posted desc
			%lmt %ofs", $paginator->getItemsPerPage(), $paginator->getOffset())
			as $commentRow) {
			$comments[] = new BlogComment($commentRow);
		}

		return isset($comments) ? $comments : [];
	}
}