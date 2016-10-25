<?php
namespace Mirin;
use Nette;
use Mirin\Model;

class BlogAuthorAuthenticator implements Nette\Security\IAuthenticator
{
	/**
	 * @var Model\BlogAuthorRepository
	 */
	private $authorRepository;

	public function __construct(Model\BlogAuthorRepository $authorRepository)
	{
		$this->authorRepository = $authorRepository;
	}

	/**
	 * Performs an authentication
	 * and returns IIdentity on success or throws AuthenticationException
	 * @return Nette\Security\IIdentity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $token, $hmacHash) = $credentials;

		$author = $this->authorRepository->getByUsername($username);
		if (!$author) {
			throw new Nette\Security\AuthenticationException("User '$username' not found.",
				Nette\Security\IAuthenticator::IDENTITY_NOT_FOUND);
		}

		if (hash_hmac("md5", $author->password_hash, "mirin.cz" . $token) != $hmacHash) {
			throw new Nette\Security\AuthenticationException("Invalid password.",
				Nette\Security\IAuthenticator::INVALID_CREDENTIAL);
		}
	}
}


