<?php
namespace Mirin\Presenters;
use Nette;
use Tracy;

class ErrorPresenter extends Nette\Application\UI\Presenter
{
	/**
	 * @inject
	 * @var Tracy\ILogger
	 */
	public $logger;

	public function renderDefault(\Exception $exception)
	{
		$template = $this->getTemplate();

		if ($exception instanceof Nette\Application\BadRequestException) {
			$template->description = "Stránka nenalezena";
			$template->message = "Požadovaná stránka nebyla nalezena."
				. " Zkontrolujte prosím zda vámi zadaná adresa je správná."
				. "<p>Pokud ne, zkuste to na <a href=\"/\">úvodní stránce</a>, nebo"
				. " zkuste stránku najít např. přes váš oblíbený vyhledávač"
				. "</p>";
			$template->type = "error " . $exception->getCode() . " - page not found";
			return;
		}

		$this->logger->log($exception, Tracy\ILogger::EXCEPTION);
		$template->description = "Server Error";
		$template->message = "We're sorry! The server encountered an internal error "
			. "was unable to complete your request. Please try again later.";
		$template->type = "error 500 - server error";
	}
}
