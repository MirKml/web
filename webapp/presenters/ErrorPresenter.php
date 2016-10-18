<?php
namespace Mirin\Presenters;

use Nette;
use Tracy\ILogger;

class ErrorPresenter implements Nette\Application\IPresenter
{
	/** @var ILogger */
	private $logger;

	public function __construct(ILogger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @return Nette\Application\IResponse
	 */
	public function run(Nette\Application\Request $request)
	{
		$e = $request->getParameter('exception');

		if ($e instanceof Nette\Application\BadRequestException) {
			list($module, , $sep) = Nette\Application\Helpers::splitName($request->getPresenterName());
			return new Nette\Application\Responses\ForwardResponse($request->setPresenterName($module . $sep . 'Error4xx'));
		}

		$this->logger->log($e, ILogger::EXCEPTION);
		return new Nette\Application\Responses\CallbackResponse(function () {
			require __DIR__ . '/templates/Error/500.phtml';
		});
	}

}
