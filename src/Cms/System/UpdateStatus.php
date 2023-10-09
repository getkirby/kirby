<?php

namespace Kirby\Cms\System;

use Composer\Semver\Semver;
use Exception;
use Kirby\Cms\App;
use Kirby\Cms\Plugin;
use Kirby\Exception\Exception as KirbyException;
use Kirby\Http\Remote;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * Checks for updates and affected vulnerabilities
 * @since 3.8.0
 *
 * @package   Kirby Cms
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UpdateStatus
{
	/**
	 * Host to request the update data from
	 */
	public static string $host = 'https://assets.getkirby.com';

	/**
	 * Marker that stores whether a previous remote
	 * request timed out
	 */
	protected static bool $timedOut = false;

	// props set in constructor
	protected App $app;
	protected string|null $currentVersion;
	protected array|null $data;
	protected string|null $pluginName;
	protected bool $securityOnly;

	// props updated throughout the class
	protected array $exceptions = [];
	protected bool|null $noVulns = null;

	// caches
	protected array $messages;
	protected array $targetData;
	protected array|bool $versionEntry;
	protected array $vulnerabilities;

	/**
	 * @param array|null $data Custom override for the getkirby.com update data
	 */
	public function __construct(
		App|Plugin $package,
		bool $securityOnly = false,
		array|null $data = null
	) {
		if ($package instanceof App) {
			$this->app        = $package;
			$this->pluginName = null;
		} else {
			$this->app        = $package->kirby();
			$this->pluginName = $package->name();
		}

		$this->securityOnly   = $securityOnly;
		$this->currentVersion = $package->version();

		$this->data = $data ?? $this->loadData();
	}

	/**
	 * Returns the currently installed version
	 */
	public function currentVersion(): string|null
	{
		return $this->currentVersion;
	}

	/**
	 * Returns the list of exception objects that were
	 * collected during data fetching and processing
	 */
	public function exceptions(): array
	{
		return $this->exceptions;
	}

	/**
	 * Returns the list of exception message strings that
	 * were collected during data fetching and processing
	 */
	public function exceptionMessages(): array
	{
		return array_map(fn ($e) => $e->getMessage(), $this->exceptions());
	}

	/**
	 * Returns the Panel icon for the status value
	 *
	 * @return string 'check'|'alert'|'info'
	 */
	public function icon(): string
	{
		return match ($this->status()) {
			'up-to-date', 'not-vulnerable' => 'check',
			'security-update', 'security-upgrade' => 'alert',
			'update', 'upgrade' => 'info',
			default => 'question'
		};
	}

	/**
	 * Returns the human-readable and translated label
	 * for the update status
	 */
	public function label(): string
	{
		return I18n::template(
			'system.updateStatus.' . $this->status(),
			'?',
			['version' => $this->targetVersion() ?? '?']
		);
	}

	/**
	 * Returns the latest available version
	 */
	public function latestVersion(): string|null
	{
		return $this->data['latest'] ?? null;
	}

	/**
	 * Returns all security messages unless no data
	 * is available
	 */
	public function messages(): array|null
	{
		if (isset($this->messages) === true) {
			return $this->messages;
		}

		if (
			$this->data === null ||
			$this->currentVersion === null ||
			$this->currentVersion === ''
		) {
			return null;
		}

		$type = $this->pluginName ? 'plugin' : 'kirby';

		// collect all matching custom messages
		$filters = [
			'kirby' => $this->app->version(),
			'php'   => phpversion()
		];

		if ($type === 'plugin') {
			$filters['plugin'] = $this->currentVersion;
		}

		$messages = $this->filterArrayByVersion(
			$this->data['messages'] ?? [],
			$filters,
			'while filtering messages'
		);

		// add a message for each vulnerability
		// the current version is affected by
		foreach ($this->vulnerabilities() as $vulnerability) {
			if ($type === 'plugin') {
				$vulnerability['plugin'] = $this->pluginName;
			}

			$messages[] = [
				'text' => I18n::template(
					'system.issues.vulnerability.' . $type,
					null,
					$vulnerability
				),
				'link' => $vulnerability['link'] ?? null,
				'icon' => 'bug'
			];
		}

		// add special message for end-of-life versions
		$versionEntry = $this->versionEntry();
		if (($versionEntry['status'] ?? null) === 'end-of-life') {
			$messages[] = [
				'text' => match ($type) {
					'plugin' => I18n::template(
						'system.issues.eol.plugin',
						null,
						['plugin' => $this->pluginName]
					),
					default => I18n::translate('system.issues.eol.kirby')
				},
				'link' => $versionEntry['status-link'] ?? 'https://getkirby.com/security/end-of-life',
				'icon' => 'bell'
			];
		}

		// add special message for end-of-life PHP versions
		$phpMajor = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
		$phpEol   = $this->data['php'][$phpMajor] ?? null;
		if (is_string($phpEol) === true && $eolTime = strtotime($phpEol)) {
			// the timestamp is available and valid, now check if it is in the past
			if ($eolTime < time()) {
				$messages[] = [
					'text' => I18n::template('system.issues.eol.php', null, ['release' => $phpMajor]),
					'link' => 'https://getkirby.com/security/php-end-of-life',
					'icon' => 'bell'
				];
			}
		}

		return $this->messages = $messages;
	}

	/**
	 * Returns the raw status value
	 *
	 * @return string 'up-to-date'|'not-vulnerable'|'security-update'|
	 *                'security-upgrade'|'update'|'upgrade'|'unreleased'|'error'
	 */
	public function status(): string
	{
		return $this->targetData()['status'];
	}

	/**
	 * Version that is suggested for the update/upgrade
	 */
	public function targetVersion(): string|null
	{
		return $this->targetData()['version'];
	}

	/**
	 * Returns the Panel theme for the status value
	 *
	 * @return string 'positive'|'negative'|'info'|'notice'
	 */
	public function theme(): string
	{
		return match ($this->status()) {
			'up-to-date', 'not-vulnerable' => 'positive',
			'security-update', 'security-upgrade' => 'negative',
			'update', 'upgrade' => 'info',
			default => 'notice'
		};
	}

	/**
	 * Returns the most important human-readable
	 * status information as array
	 */
	public function toArray(): array
	{
		return [
			'currentVersion' => $this->currentVersion() ?? '?',
			'icon'           => $this->icon(),
			'label'          => $this->label(),
			'latestVersion'  => $this->latestVersion() ?? '?',
			'pluginName'     => $this->pluginName,
			'theme'          => $this->theme(),
			'url'            => $this->url(),
		];
	}

	/**
	 * URL of the target version with fallback
	 * to the URL of the current version;
	 * `null` is returned if no URL is known
	 */
	public function url(): string|null
	{
		return $this->targetData()['url'];
	}

	/**
	 * Returns all vulnerabilities the current version
	 * is affected by unless no data is available
	 */
	public function vulnerabilities(): array|null
	{
		if (isset($this->vulnerabilities) === true) {
			return $this->vulnerabilities;
		}

		if (
			$this->data === null ||
			$this->currentVersion === null ||
			$this->currentVersion === ''
		) {
			return null;
		}

		// shortcut for versions without vulnerabilities
		$this->versionEntry();
		if ($this->noVulns === true) {
			return $this->vulnerabilities = [];
		}

		// unstable releases are released before their respective
		// stable release and would not be matched by the constraints,
		// but they will likely also contain the same vulnerabilities;
		// so we strip off any non-numeric version modifiers from the end
		preg_match('/^([0-9.]+)/', $this->currentVersion, $matches);
		$currentVersion = $matches[1];

		$vulnerabilities = $this->filterArrayByVersion(
			$this->data['incidents'] ?? [],
			['affected' => $currentVersion],
			'while filtering incidents'
		);

		// sort the vulnerabilities by severity (with critical first)
		$severities = array_map(
			fn ($vulnerability) => match ($vulnerability['severity'] ?? null) {
				'critical' => 4,
				'high'     => 3,
				'medium'   => 2,
				'low'      => 1,
				default    => 0
			},
			$vulnerabilities
		);
		array_multisort($severities, SORT_DESC, $vulnerabilities);

		return $this->vulnerabilities = $vulnerabilities;
	}

	/**
	 * Compares a version against a Composer version constraint
	 * and returns whether the constraint is satisfied
	 *
	 * @param string $reason Suffix for error messages
	 */
	protected function checkConstraint(string $version, string $constraint, string $reason): bool
	{
		try {
			return Semver::satisfies($version, $constraint);
		} catch (Exception $e) {
			$package = $this->packageName();
			$message = 'Error comparing version constraint for ' . $package . ' ' . $reason . ': ' . $e->getMessage();

			$exception = new KirbyException([
				'fallback' => $message,
				'previous' => $e
			]);
			$this->exceptions[] = $exception;

			return false;
		}
	}

	/**
	 * Filters a two-level array with one or multiple version constraints
	 * for each value by one or multiple version filters;
	 * values that don't contain the filter keys are removed
	 *
	 * @param array $array Array that contains associative arrays
	 * @param array $filters Associative array `field => version`
	 * @param string $reason Suffix for error messages
	 */
	protected function filterArrayByVersion(array $array, array $filters, string $reason): array
	{
		return array_filter($array, function ($item) use ($filters, $reason): bool {
			foreach ($filters as $key => $version) {
				if (isset($item[$key]) !== true) {
					$package = $this->packageName();
					$this->exceptions[] = new KirbyException('Missing constraint ' . $key . ' for ' . $package . ' ' . $reason);

					return false;
				}

				if ($this->checkConstraint($version, $item[$key], $reason) !== true) {
					return false;
				}
			}

			return true;
		});
	}

	/**
	 * Finds the minimum possible security update
	 * to fix all known vulnerabilities
	 *
	 * @return string|null Version number of the update or
	 *                     `null` if no free update is possible
	 */
	protected function findMinimumSecurityUpdate(): string|null
	{
		$versionEntry = $this->versionEntry();
		if ($versionEntry === null || isset($versionEntry['latest']) !== true) {
			return null; // @codeCoverageIgnore
		}

		$affected   = $this->vulnerabilities();
		$incidents  = $this->data['incidents'] ?? [];
		$maxVersion = $versionEntry['latest'];

		// increase the target version number until there are no vulnerabilities
		$version    = $this->currentVersion;
		$iterations = 0;
		while (empty($affected) === false) {
			// protect against infinite loops if the
			// input data is contradicting itself
			$iterations++;
			if ($iterations > 10) {
				return null;
			}

			// if we arrived at the `$maxVersion` but still haven't found
			// a version without vulnerabilities, we cannot suggest a version
			if ($version === $maxVersion) {
				return null;
			}

			// find the minimum version that fixes all affected vulnerabilities
			foreach ($affected as $incident) {
				$incidentVersion = null;
				foreach (Str::split($incident['fixed'], ',') as $fixed) {
					// skip versions of other major releases
					if (
						version_compare($fixed, $this->currentVersion, '<') === true ||
						version_compare($fixed, $maxVersion, '>') === true
					) {
						continue;
					}

					// find the minimum version that fixes this specific vulnerability
					if (
						$incidentVersion === null ||
						version_compare($fixed, $incidentVersion, '<') === true
					) {
						$incidentVersion = $fixed;
					}
				}

				// verify that we found at least one possible version;
				// otherwise try the `$maxVersion` as a last chance before
				// concluding at the top that we cannot solve the task
				$incidentVersion ??= $maxVersion;

				// we need a version that fixes all vulnerabilities, so use the
				// "largest of the smallest" fixed versions
				if (version_compare($incidentVersion, $version, '>') === true) {
					$version = $incidentVersion;
				}
			}

			// run another loop to verify that the suggested version
			// doesn't have any known vulnerabilities on its own
			$affected = $this->filterArrayByVersion(
				$incidents,
				['affected' => $version],
				'while filtering incidents'
			);
		}

		return $version;
	}

	/**
	 * Loads the getkirby.com update data
	 * from cache or via HTTP
	 */
	protected function loadData(): array|null
	{
		// try to get the data from cache
		$cache = $this->app->cache('updates');
		$key   = (
			$this->pluginName ?
			'plugins/' . $this->pluginName :
			'security'
		);

		// try to return from cache;
		// invalidate the cache after updates
		$data = $cache->get($key);
		if (
			is_array($data) === true &&
			$data['_version'] === $this->currentVersion
		) {
			return $data;
		}

		// exception message (on previous request error)
		if (is_string($data) === true) {
			// restore the exception to make it visible when debugging
			$this->exceptions[] = new KirbyException($data);

			return null;
		}

		// before we request the data, ensure we have a writable cache;
		// this reduces strain on the CDN from repeated requests
		if ($cache->enabled() === false) {
			$this->exceptions[] = new KirbyException('Cannot check for updates without a working "updates" cache');

			return null;
		}

		// first catch every exception;
		// we collect it below for debugging
		try {
			if (static::$timedOut === true) {
				throw new Exception('Previous remote request timed out'); // @codeCoverageIgnore
			}

			$response = Remote::get(
				static::$host . '/' . $key . '.json',
				['timeout' => 2]
			);

			// allow status code HTTP 200 or 0 (e.g. for the file:// protocol)
			if (in_array($response->code(), [0, 200], true) !== true) {
				throw new Exception('HTTP error ' . $response->code()); // @codeCoverageIgnore
			}

			$data = $response->json();

			if (is_array($data) !== true) {
				throw new Exception('Invalid JSON data');
			}
		} catch (Exception $e) {
			$package = $this->packageName();
			$message = 'Could not load update data for ' . $package . ': ' . $e->getMessage();

			$exception = new KirbyException([
				'fallback' => $message,
				'previous' => $e
			]);
			$this->exceptions[] = $exception;

			// if the request timed out, prevent additional
			// requests for other packages (e.g. plugins)
			// to avoid long Panel hangs
			if ($e->getCode() === 28) {
				static::$timedOut = true; // @codeCoverageIgnore
			} elseif (static::$timedOut === false) {
				// different error than timeout;
				// prevent additional requests in the
				// next three days (e.g. if a plugin
				// does not have a page on getkirby.com)
				// by caching the exception message
				// instead of the data array
				$cache->set($key, $exception->getMessage(), 3 * 24 * 60);
			}

			return null;
		}

		// also cache the current version to
		// invalidate the cache after updates
		// (ensures that the update status is
		// fresh directly after the update to
		// avoid confusion with outdated info)
		$data['_version'] = $this->currentVersion;

		// cache the retrieved data for three days
		$cache->set($key, $data, 3 * 24 * 60);

		return $data;
	}

	/**
	 * Returns the human-readable package name for error messages
	 */
	protected function packageName(): string
	{
		return $this->pluginName ? 'plugin ' . $this->pluginName : 'Kirby';
	}

	/**
	 * Performs the update check and returns data for the
	 * target version (with fallback and error handling)
	 */
	protected function targetData(): array
	{
		if (isset($this->targetData) === true) {
			return $this->targetData;
		}

		// check if we have valid data to compare to
		$versionEntry = $this->versionEntry();
		if ($versionEntry === null) {
			$version = $this->currentVersion ?? $this->data['latest'] ?? null;

			return $this->targetData = [
				'status'  => 'error',
				'url'     => $version ? $this->urlFor($version, 'changes') : null,
				'version' => null
			];
		}

		// check if the current version is the latest available
		if (($versionEntry['status'] ?? null) === 'latest') {
			return $this->targetData = [
				'status'  => 'up-to-date',
				'url'     => $this->urlFor($this->currentVersion, 'changes'),
				'version' => null
			];
		}

		// check if the current version is unreleased
		if (($versionEntry['status'] ?? null) === 'unreleased') {
			return $this->targetData = [
				'status'  => 'unreleased',
				'url'     => null,
				'version' => null
			];
		}

		// check if the installation is vulnerable;
		// minimum possible security fixes are preferred
		// over all other updates and upgrades
		if (count($this->vulnerabilities()) > 0) {
			$update = $this->findMinimumSecurityUpdate();

			if ($update !== null) {
				// a free security update was found
				return $this->targetData = [
					'status'  => 'security-update',
					'url'     => $this->urlFor($update, 'changes'),
					'version' => $update
				];
			}

			// only a paid security upgrade is possible
			return $this->targetData = [
				'status'  => 'security-upgrade',
				'url'     => $this->urlFor($this->currentVersion, 'upgrade'),
				'version' => $this->data['latest'] ?? null
			];
		}

		// check if the user limited update checking to security updates
		if ($this->securityOnly === true) {
			return $this->targetData = [
				'status'  => 'not-vulnerable',
				'url'     => $this->urlFor($this->currentVersion, 'changes'),
				'version' => null
			];
		}

		// check if free updates are possible from the current version
		$latest = $versionEntry['latest'] ?? null;
		if (is_string($latest) === true && $latest !== $this->currentVersion) {
			return $this->targetData = [
				'status'  => 'update',
				'url'     => $this->urlFor($latest, 'changes'),
				'version' => $latest
			];
		}

		// no free update is possible, but we are not on the latest version,
		// so the overall latest version must be an upgrade
		return $this->targetData = [
			'status'  => 'upgrade',
			'url'     => $this->urlFor($this->currentVersion, 'upgrade'),
			'version' => $this->data['latest'] ?? null
		];
	}

	/**
	 * Returns the URL for a specific version and purpose
	 */
	protected function urlFor(string $version, string $purpose): string|null
	{
		if ($this->data === null) {
			return null;
		}

		// find the first matching entry
		$url = null;
		foreach ($this->data['urls'] ?? [] as $constraint => $entry) {
			// filter out every entry that does not match the version
			if ($this->checkConstraint($version, $constraint, 'while finding URL') !== true) {
				continue;
			}

			// we found a result
			$url = $entry[$purpose] ?? null;
			if ($url !== null) {
				break;
			}
		}

		if ($url === null) {
			$package = $this->packageName();
			$message = 'No matching URL found for ' . $package . '@' . $version;

			$this->exceptions[] = new KirbyException($message);

			return null;
		}

		// insert the URL template placeholders
		return Str::template($url, [
			'current' => $this->currentVersion,
			'version' => $version
		]);
	}

	/**
	 * Extracts the first matching version entry from
	 * the data array unless no data is available
	 */
	protected function versionEntry(): array|null
	{
		if (isset($this->versionEntry) === true) {
			// no version entry found on last call
			if ($this->versionEntry === false) {
				return null;
			}

			return $this->versionEntry;
		}

		if (
			$this->data === null ||
			$this->currentVersion === null ||
			$this->currentVersion === ''
		) {
			return null;
		}

		// special check for unreleased versions
		$latest = $this->data['latest'] ?? null;
		if (
			$latest !== null &&
			version_compare($this->currentVersion, $latest, '>') === true
		) {
			return [
				'status' => 'unreleased'
			];
		}

		$versionEntry = null;
		foreach ($this->data['versions'] ?? [] as $constraint => $entry) {
			// filter out every entry that does not match the current version
			if ($this->checkConstraint($this->currentVersion, $constraint, 'while finding version entry') !== true) {
				continue;
			}

			if (($entry['status'] ?? null) === 'no-vulnerabilities') {
				$this->noVulns = true;

				// use the next matching version entry with
				// more specific update information
				continue;
			}

			if (($entry['status'] ?? null) === 'latest') {
				$this->noVulns = true;
			}

			// we found a result
			$versionEntry = $entry;
			break;
		}

		if ($versionEntry === null) {
			$package = $this->packageName();
			$message = 'No matching version entry found for ' . $package . '@' . $this->currentVersion;

			$this->exceptions[] = new KirbyException($message);
		}

		$this->versionEntry = $versionEntry ?? false;
		return $versionEntry;
	}
}
