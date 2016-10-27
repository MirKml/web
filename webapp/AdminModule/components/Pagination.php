<?php
namespace Mirin\AdminModule\Components;
use Mirin;

/**
 * Pagination for blog administration
 * @package Mirin\AdminModule\Components
 */
class Pagination extends Mirin\Components\Pagination
{
	protected function getTemplateFile()
	{
		return __DIR__ . "/templates/pagination.latte";
	}
}
