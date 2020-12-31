<?php

namespace Kirby\ComposerInstaller;

use Composer\Config;
use Composer\Package\PackageInterface;
use InvalidArgumentException;

/**
 * @package   Kirby Composer Installer
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class CmsInstaller extends Installer
{
    /**
     * Decides if the installer supports the given type
     *
     * @param string $packageType
     * @return bool
     */
    public function supports($packageType): bool
    {
        return $packageType === 'kirby-cms';
    }

    /**
     * Returns the installation path of a package
     *
     * @param \Composer\Package\PackageInterface $package
     * @return string
     */
    public function getInstallPath(PackageInterface $package): string
    {
        // get the extra configuration of the top-level package
        if ($rootPackage = $this->composer->getPackage()) {
            $extra = $rootPackage->getExtra();
        } else {
            $extra = [];
        }

        // use path from configuration, otherwise fall back to default
        if (isset($extra['kirby-cms-path']) === true) {
            $path = $extra['kirby-cms-path'];
        } else {
            $path = 'kirby';
        }

        // if explicitly set to something invalid (e.g. `false`), install to vendor dir
        if (is_string($path) !== true) {
            return parent::getInstallPath($package);
        }

        // don't allow unsafe directories
        $vendorDir = $this->composer->getConfig()->get('vendor-dir', Config::RELATIVE_PATHS) ?? 'vendor';
        if ($path === $vendorDir || $path === '.') {
            throw new InvalidArgumentException('The path ' . $path . ' is an unsafe installation directory for ' . $package->getPrettyName() . '.');
        }

        return $path;
    }
}
