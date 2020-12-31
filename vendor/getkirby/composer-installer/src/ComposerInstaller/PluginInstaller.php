<?php

namespace Kirby\ComposerInstaller;

use Composer\Package\PackageInterface;
use InvalidArgumentException;

/**
 * @package   Kirby Composer Installer
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class PluginInstaller extends Installer
{
    /**
     * Decides if the installer supports the given type
     *
     * @param string $packageType
     * @return bool
     */
    public function supports($packageType): bool
    {
        return $packageType === 'kirby-plugin';
    }

    /**
     * Returns the installation path of a package
     *
     * @param \Composer\Package\PackageInterface $package
     * @return string path
     */
    public function getInstallPath(PackageInterface $package): string
    {
        // place into `vendor` directory as usual if Pluginkit is not supported
        if ($this->supportsPluginkit($package) !== true) {
            return parent::getInstallPath($package);
        }

        // get the extra configuration of the top-level package
        if ($rootPackage = $this->composer->getPackage()) {
            $extra = $rootPackage->getExtra();
        } else {
            $extra = [];
        }

        // use base path from configuration, otherwise fall back to default
        $basePath = $extra['kirby-plugin-path'] ?? 'site/plugins';

        if (is_string($basePath) !== true) {
            throw new InvalidArgumentException('Invalid "kirby-plugin-path" option');
        }

        // determine the plugin name from its package name;
        // can be overridden in the plugin's `composer.json`
        $prettyName = $package->getPrettyName();
        $pluginExtra = $package->getExtra();
        if (empty($pluginExtra['installer-name']) === false) {
            $name = $pluginExtra['installer-name'];

            if (is_string($name) !== true) {
                throw new InvalidArgumentException('Invalid "installer-name" option in plugin ' . $prettyName);
            }
        } elseif (strpos($prettyName, '/') !== false) {
            // use name after the slash
            $name = explode('/', $prettyName)[1];
        } else {
            $name = $prettyName;
        }

        // build destination path from base path and plugin name
        return $basePath . '/' . $name;
    }

    /**
     * Custom handler that will be called after each package
     * installation or update
     *
     * @param \Composer\Package\PackageInterface $package
     * @return void
     */
    protected function postInstall(PackageInterface $package): void
    {
        // only continue if Pluginkit is supported
        if ($this->supportsPluginkit($package) !== true) {
            return;
        }

        parent::postInstall($package);
    }

    /**
     * Checks if the package has explicitly required this installer;
     * otherwise (if the Pluginkit is not yet supported by the plugin)
     * the installer will fall back to the behavior of the LibraryInstaller
     *
     * @param \Composer\Package\PackageInterface $package
     * @return bool
     */
    protected function supportsPluginkit(PackageInterface $package): bool
    {
        foreach ($package->getRequires() as $link) {
            if ($link->getTarget() === 'getkirby/composer-installer') {
                return true;
            }
        }

        // no required package is the installer
        return false;
    }
}
