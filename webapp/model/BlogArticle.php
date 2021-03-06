<?php
namespace Mirin\Model;
use Dibi;

class BlogArticle
{
	public $id;
	public $text;
	public $plainText;
	public $title;
	public $titleUrl;
	/**
	 * @var \DateTime
	 */
	public $posted;
	public $authorName;
	public $format;
	public $commentsCount;

	/**
	 * @var $description
	 */
	private $description;

	/**
	 * @var string
	 */
	private $htmlText;

	/**
	 * @var \WikiText_Parser
	 */
	private $wikiParser;

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
	public function getHtmlText()
	{
		if ($this->htmlText) return $this->htmlText;

		if ($this->format == "html") {
			return $this->htmlText = self::escapeCodeTagContent($this->text);
		}

		// clear handler calls, necessary, because parser is persistent
		$this->getWikiParser()->getHandler()->reset();
		$instructions = $this->getWikiParser()->parse($this->text);

		$renderer = new \WikiText_Renderer_Xhtml();
		foreach ($instructions as $instruction) {
			call_user_func_array(array($renderer, $instruction[0]), $instruction[1]);
		}

		return $this->htmlText = $renderer->getOutput();
	}

	public function getRssContent($baseUri, $articleUri)
	{
		$htmlText = $this->getHtmlText();

		// resolves local links like <a href="/homepage/blog">link</a> into
		// <a href="http://mysite/homepage/blog">link</a>
		$htmlText = preg_replace("/\s(href|src)=\"\//"," $1=\"$baseUri/", $htmlText);

		// resolves local links for the footnoteslike <a href="#fnt">link</a> into
		// <a href="http://mysite/homepage/blog#fnt">link</a>
		$htmlText = preg_replace("/<a href=\"#/","<a href=\"$articleUri#", $htmlText);
		return self::escapeCodeTagContent($htmlText);
	}

	private function getWikiParser()
	{
		if ($this->wikiParser) return $this->wikiParser;

		$parser = new \WikiText_Parser();
		$parser->setHandler(new \WikiText_Handler());

		// add modes to parser
		// add default modes
		$parser->addMode('listblock', new \WikiText_Parser_Mode_ListBlock());
		$parser->addMode('preformatted', new \WikiText_Parser_Mode_Preformatted());
		$parser->addMode('unformatted',new \WikiText_Parser_Mode_UnFormatted());
		$parser->addMode('header',new \WikiText_Parser_Mode_Header());
		$parser->addMode('table',new \WikiText_Parser_Mode_Table());
		$parser->addMode('linebreak',new \WikiText_Parser_Mode_LineBreak());
		$parser->addMode('footnote',new \WikiText_Parser_Mode_FootNote());
		$parser->addMode('externallink',new \WikiText_Parser_Mode_ExternalLink());
		$parser->addMode('html',new \WikiText_Parser_Mode_Html());
		$parser->addMode('quote',new \WikiText_Parser_Mode_Quote());
		$parser->addMode('code',new \WikiText_Parser_Mode_Code());
		$parser->addMode('internallink',new \WikiText_Parser_Mode_InternalLink());
		$parser->addMode('emaillink',new \WikiText_Parser_Mode_EmailLink());
		$parser->addMode('media',new \WikiText_Parser_Mode_Media());
		$parser->addMode('eol',new \WikiText_Parser_Mode_Eol());

		// add formatting modes
		$parser->addMode('strong',new \WikiText_Parser_Mode_Formatting('strong'));
		$parser->addMode('emphasis',new \WikiText_Parser_Mode_Formatting('emphasis'));
		$parser->addMode('underline',new \WikiText_Parser_Mode_Formatting('underline'));
		$parser->addMode('monospace',new \WikiText_Parser_Mode_Formatting('monospace'));
		$parser->addMode('subscript',new \WikiText_Parser_Mode_Formatting('subscript'));
		$parser->addMode('superscript',new \WikiText_Parser_Mode_Formatting('superscript'));
		$parser->addMode('deleted',new \WikiText_Parser_Mode_Formatting('deleted'));

		// add modes which need files
		$parser->addMode('smiley',new \WikiText_Parser_Mode_Smiley());
		$parser->addMode('acronym',new \WikiText_Parser_Mode_Acronym());
		$parser->addMode('entity',new \WikiText_Parser_Mode_Entity());

		return $this->wikiParser = $parser;
	}

	/**
	 * Makes html special chars conversion for all text in the <code> tag for a whole
	 * article text.
	 * @param string $htmlText article text as the html
	 * @return string
	 */
	private static function escapeCodeTagContent($htmlText)
	{
		$pattern = "/<code>((\\n|.)*)<\/code>/U";
		$sanitizedHtml = preg_replace_callback($pattern, function($matches) {
			return "<code>" . htmlspecialchars($matches[1]) . "</code>";
		}, $htmlText);
		return $sanitizedHtml;

		/* alternatively, with non-transparent $replacement, stripslashes in a replacement
		needed because /e regexp modifier slashes all ' "
		$pattern = "/<code>((\\n|.)*)<\/code>/eU";
		$replacement="'<code>'.htmlspecialchars(stripslashes('\\1'),ENT_NOQUOTES).'</code>'";
		$text=preg_replace($pattern,$replacement,$articleText);
		*/
	}

	/**
	 * Returns the description for the article as html text.
	 * It's mostly first paragraph from the text.
	 * @return string
	 */
	public function getDescription()
	{
		if ($this->description) return $this->description;

		preg_match("/<p>.*<\/p>/sU", $this->getHtmlText(), $matches);
		return $this->description = $matches[0];
	}
}
