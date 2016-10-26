<?php
namespace Mirin\Model;

class BlogAuthorRepository
{

	/**
	 * @var \Dibi\Connection
	 */
	private $db;

	public function __construct(\Dibi\Connection $db)
	{
		$this->db = $db;
	}

	/**
	 * @param string $username
	 * @return object
	 */
	public function getByUsername($username)
	{
		return $this->db->fetch("select * from author where username = %s", $username);
	}

	/**
	 * @param int $id
	 * @return object
	 */
	public function getById($id)
	{
		return $this->db->fetch("select * from author where id = %i", $id);
	}

	/**
	 * Gets all author names as array with ID as key for each one.
	 * It's mostly used for select boxes with authors.
	 * @return array
	 */
	public function getAllForSelectBox()
	{
		return $this->db->query("select * from author")
			->fetchAssoc("id|=name");
	}
}
