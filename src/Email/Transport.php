<?php

namespace Kirby\Email;

use Kirby\Exception\InvalidArgumentException;
use SensitiveParameter;

/**
 * Email transports settings for mailer
 *
 * @package   Kirby Email
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     5.0.0
 */
class Transport
{
	public function __construct(
		public string $type = 'mail',
		public string|null $host = null,
		public int|null $port = null,
		public string|bool $security = 'ssl',
		public bool $auth = false,
		#[SensitiveParameter]
		public string|null $username = null,
		#[SensitiveParameter]
		public string|null $password = null,
	) {
	}

	public function auth(): bool
	{
		return $this->auth;
	}

	public function host(): string|null
	{
		return $this->host;
	}

	public function password(): string|null
	{
		return $this->password;
	}

	public function port(): int|null
	{
		if ($this->type() === 'mail') {
			return null;
		}

		// fallback to match security option
		return $this->port ?? match ($this->security()) {
			'tls'   => 587,
			'ssl'   => 465,
			default => null
		};
	}

	public function security(): string|null
	{
		if ($this->type() === 'mail') {
			return null;
		}

		// automatic mode: try to set based on port
		if ($this->security === true) {
			return match ($this->port) {
				null, 587 => 'tls',
				465       => 'ssl',
				default   => throw new InvalidArgumentException(
					'Could not automatically detect the "security" protocol from the "port" option, please set it explicitly to "tls" or "ssl".'
				)
			};
		}

		return $this->security;
	}

	public function toArray(): array
	{
		return array_filter([
			'type'     => $this->type(),
			'host'     => $this->host(),
			'port'     => $this->port(),
			'security' => $this->security(),
			'auth'     => $this->auth(),
			'username' => $this->username(),
			'password' => $this->password(),
		]);
	}

	public function type(): string
	{
		return $this->type;
	}

	public function username(): string|null
	{
		return $this->username;
	}
}
