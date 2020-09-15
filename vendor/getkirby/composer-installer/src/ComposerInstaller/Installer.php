<?php

namespace Kirby\ComposerInstaller;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use React\Promise\PromiseInterface;
use RuntimeException;

/**
 * @package   Kirby Composer Installer
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Installer extends LibraryInstaller
{
    /**
     * Decides if the installer supports the given type
     *
     * @param string $packageType
     * @return bool
     */
    public function supports($packageType): bool
    {
        throw new RuntimeException('This method needs to be overridden.'); // @codeCoverageIgnore
    }

    /**
     * Installs specific package.
     *
     * @param InstalledRepositoryInterface $repo repository in which to check
     * @param PackageInterface $package package instance
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        // first install the package normally...
        $promise = parent::install($repo, $package);

        // ...then run custom code
        $postInstall = function () use ($package) {
            $this->postInstall($package);
        };

        // Composer 2 in async mode
        if ($promise instanceof PromiseInterface) {
            return $promise->then($postInstall);
        }

        // Composer 1 or Composer 2 without async
        $postInstall();
    }

    /**
     * Updates specific package.
     *
     * @param InstalledRepositoryInterface $repo repository in which to check
     * @param PackageInterface $initial already installed package version
     * @param PackageInterface $target updated version
     *
     * @throws InvalidArgumentException if $initial package is not installed
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        // first update the package normally...
        $promise = parent::update($repo, $initial, $target);

        // ...then run custom code
        $postInstall = function () use ($target) {
            $this->postInstall($target);
        };

        // Composer 2 in async mode
        if ($promise instanceof PromiseInterface) {
            return $promise->then($postInstall);
        }

        // Composer 1 or Composer 2 without async
        $postInstall();
    }

    /**
     * Custom handler that will be called after each package
     * installation or update
     *
     * @param PackageInterface $package
     */
    protected function postInstall(PackageInterface $package)
    {
        // remove the package's `vendor` directory to avoid duplicated autoloader and vendor code
        $packageVendorDir = $this->getInstallPath($package) . '/vendor';
        if (is_dir($packageVendorDir)) {
            $success = $this->filesystem->removeDirectory($packageVendorDir);
            if (!$success) {
                throw new RuntimeException('Could not completely delete ' . $path . ', aborting.'); // @codeCoverageIgnore
            }
        }
    }
}
