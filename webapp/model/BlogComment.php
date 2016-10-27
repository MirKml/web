<?php
namespace Mirin\Model;
use Dibi;

class BlogComment
{
	public $id;
	public $article_id;
	public $articleTitle;
	public $name;
	public $email;
	public $message;
	public $www;

	/**
	 * @var \DateTime
	 */
	public $posted;

	/**
	 * @param Dibi\Row
	 */
	public function __construct(Dibi\Row $row)
	{
		foreach ($row->toArray() as $name => $value) {
			$this->$name = $value;
		}
	}

	/**
	 * @return string
	 */
	public function getHtmlMessage()
	{
		// replace line breaks with <br \>
		$text = nl2br($this->message);

		// remove <br /> from the <pre> tags
		$pattern = "/<pre>((\\n|.)*)<\/pre>/U";
		$text = preg_replace_callback($pattern, function($matches) {
			return "<pre>" . str_replace("<br />", "" , $matches[1]) . "</pre>";
		}, $text);

		return $text;
	}

	/**
	 * @return string|void
	 */
	public function getAuthorWeb()
	{
		if (!$this->www) return;
		if (strpos($this->www, "http://") === 0 || strpos($this->www, "https://") === 0) {
			return $this->www;
		}
		return "http://" . $this->www;
	}
}
