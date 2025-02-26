<?php

namespace Kirby\Plugin;

/**
 * Autoloader 
 */

use Closure;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\A;
use Kirby\Data\Data;
use Kirby\Cms\App;
use Kirby\Cms\AppPlugins;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;
use ReflectionMethod;

class Autoloader
{

    private ?string $cache_file;
    private bool|array $cache = false;
    private ?array $cache_resistance = null;
    public array $classes = [];
    
    /**
     * Contains the default list of tasks to autoload
     * First argument is the method name followed by the arguments
     * 
     * @var array
     */
    public array $tasks = [
        'cache'   => [
            'method'    => 'loadCache',
            'path'      => './'
        ],
        'classes' => [
            'method'    => 'loadClasses',
            'path'      => './classes',
            'namespace' => null
        ],
        'config' => [
            'method'    => 'deepWalker',
            'path'      => './config'
        ],
        'fields' => [
            'method'    => 'flatWalker',
            'path'      => './fields',
            'rootkey'   => 'fields',
            'read'      => true
        ],
        'sections' => [
            'method'    => 'flatWalker',
            'path'      => './sections',
            'rootkey'   => 'sections',
            'read'      => true
        ],
        'blueprints' => [
            'method'    => 'flatWalker',
            'path'      => './blueprints',
            'rootkey'   => 'blueprints',
            'read'      => true
        ],
        'snippets' => [
            'method'    => 'flatWalker',
            'path'      => './snippets',
            'rootkey'   => 'snippets',
            'read'      => false
        ],
        'templates' => [
            'method'    => 'flatWalker',
            'path'      => './templates',
            'rootkey'   => 'templates',
            'read'      => true
        ],
        'translations' => [
            'method'    => 'flatWalker',
            'path'      => './i18n',
            'rootkey'   => 'translations'
        ]
    ];

    /**
     * @param string $name Plugin name
     * @param string $root The root path of the plugin
     * @param array $data Predefined extension data to extend
     * @param array|bool $tasks A list of tasks to autoload (see $tasks property)
     * @return void|$this 
     */
    public function __construct(
        private string $name,
        private string $root,
        private array $data = [],
        array|bool $tasks = true
    ) {

        //Skip Autoloader
        if ($tasks === false) {
            return;
        }

        $this->setUserTasks($tasks);

        foreach ($this->tasks as $task) {
            $method = $task['method'];

            //Call given user function
            if ($method instanceof Closure) {
                $method($this);
                continue;
            }            


            $reflection = new ReflectionMethod($this, $method);

            //Only pubilc methods could be loadet
            if ($reflection->isPublic() === false) {
                throw new InvalidArgumentException("Cannot use '{$method}' for autoloader tasks.");
            }

            //Remove methodname from array and call task
            unset($task['method']);
            call_user_func([$this, $method], ...$task);

            //Cache is lodaet -> escape
            if ($this->cache === true) {
                return $this;
            }
        }

        $this->saveCache();

        return $this;
    }

    /**
     * Modify the default tasks with an array
     * 
     * @param bool|array $tasks 
     * @return void 
     */
    private function setUserTasks(bool|array $user_tasks): void
    {
        //Enable all
        if (is_array($user_tasks) === false) {
            $user_tasks = array_keys($this->tasks);
        }

        //Cache and classes needs to be first
        $user_tasks = A::merge([
            'cache'     => true,
            'classes'   => false
        ], $user_tasks);

        foreach ($user_tasks as $key => &$task) {

            //Activate task without key
            if (is_numeric($key) && is_string($task)) {
                $key = $task;
                $task = true;
            }

            //Take from default
            if ($task === null || $task === true) {
                $task = [];
            }

            //Disable item
            if ($task === false) {
                unset($user_tasks[$key]);
                continue;
            }

            //Pass new path
            if (is_string($task)) {
                $task['path'] = $task;
            }

            //Merge array with default
            if (is_array($task)) {
                $task = A::merge($this->tasks[$key] ?? [], $task);
            }

            //Make absolute
            if (array_key_exists('path', $task) && Str::startsWith($task['path'], '.')) {
                $task['path'] = $this->root . Str::ltrim($task['path'], '.');
            }
        }

        //Unset the reference
        unset($task);
        $this->tasks = $user_tasks;

    }

    /**
     * Run autoloader and return the results
     * 
     * @param mixed ...$params 
     * @return array 
     */
    public static function load(...$params): array
    {
        $self = new self(...$params);
        return $self->toArray();
    }

    /**
     * Load the extension from the cache file.
     * 
     * @param string $path Path to check the modified time
     * @param string $cache_folder By default 'site/cache/autoloader/plugin_vendor/plugin_name/'
     * @return void 
     */
    public function loadCache(
        string $path, 
        string $cache_folder = null
    ): void
    {

        $cache_folder ??= App::instance()->root('cache') . "/autoloader/{$this->name}";
        $this->cache_file = $cache_folder . '/' . Dir::modified($path) . '.php';

        //No cache -> continue autoload
        if (F::exists($this->cache_file) === false) {
            //Enable Caching
            $this->cache = [];
            return;
        }

        try {
            $cache = Data::read($this->cache_file);
        } catch (\Throwable $th) {
            $error = $th->getMessage();
            throw new Exception("Error reading autoload cache: {$error}");
        };

        //Load classes
        $this->registerClasses($cache['classes']);

        //Merge cache to data
        $this->merge($cache['data']);

        //Load resistant cache items to data
        if ($this->cache_resistance = $cache['resistance'] ?? null) {
            foreach ($this->cache_resistance as $file => $keychain) {
                $this->setValueFromKeyChain($this->data, $keychain, Data::read($file));
            };
        };

        //Indicate that cache is loadet
        $this->cache = true;
    }

    /**
     * Set value to data (and cache) with a chain of keys
     * 
     * @param string|array $key_chain 
     * @param string $file 
     * @param bool $read True sets the content of the file otherwise the path to the file
     * @return void 
     */
    private function setValue(string|array $key_chain, string $file, bool $read = true): void
    {

        //Make shure $key_chain is an array
        $key_chain = A::wrap($key_chain);

        //Sets file or filename to value
        $value = ($read) ? Data::read($file) : $file;

        //Value may be a closure
        if (is_array($value) && count($value) === 0) {
            $chainstring = A::join($key_chain, ' -> ');
            throw new Exception("The value for $chainstring is empty and cannot be resolved by the autoloader");
        }

        //Check rootkey is allowed for extension
        if (in_array($key_chain[0] ?? null, App::instance()->allowedExtensionsKeys()) === false) {
            throw new Exception("'$key_chain[0]' is not a valid extension type", 1);
        }

        //Add value to data
        $this->setValueFromKeyChain($this->data, $key_chain, $value);

        //Cache is disabled
        if ($this->cache === false) {
            return;
        }

        if (is_string($value) || $this->isCacheable($value)) {
            //Value can be stored in cache
            $this->setValueFromKeyChain($this->cache, $key_chain, $value);
        } else {
            //Add filename to the non-cachable array
            $this->cache_resistance[$file] = $key_chain;
        }
    }

    /**
     * Walk through the key chain an sets the value to the given array
     * 
     * @param array &$array 
     * @param array $key_chain 
     * @param mixed $value 
     * @return void 
     */
    private function setValueFromKeyChain(array &$array, array $key_chain, $value)
    {
        $array ??= $this->cache;

        //Reference to the array
        $temp = &$array;

        foreach ($key_chain as $key) {

            //Set pseudo value if array not exists
            if (is_array($temp[$key] ?? null) === false) {
                $temp[$key] = null;
            }

            $temp = &$temp[$key];
        }

        //End of chain -> set the value
        $temp ??= $value;
    }

    
    /**
     * Check if the file/folder name not starts with '_'
     * 
     * @param string $path 
     * @return null|string 
     */
    private function isActive(string $path): ?string
    {
        return Str::startsWith(pathinfo($path, PATHINFO_FILENAME), '_') === false;
    }

    /**
     * Get absolute path and returns a string or an array of the subfolders
     * 
     * @param string $root 
     * @param string $path 
     * @param null|string $separator If set the subfolder will be glued with this value 
     * @return array|string 
     */
    private function keyFromPath(string $root, string $path, ?string $separator = null): array|string
    {
        $diff = Str::ltrim($path, $root . '/');
        $key = substr($diff, 0, strrpos($diff, '.'));
        if ($separator) {
            return Str::replace($key, '/', $separator);
        }
        return Str::split($key, '/');
    }

    /**
     * Returns the extension data
     * 
     *  @return array  */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Merge aray to the extension data
     * 
     * @param null|array $array 
     * @return void 
     */
    public function merge(?array $array)
    {
        $this->data = A::merge($this->data, $array);
    }

    /**
     * Walk throw the given directory recursively and pass the filename to the callback
     * Check if files doesn't starts with '_'
     * 
     * @param null|string $path 
     * @param null|Closure $callback 
     * @param bool $read
     * @return void 
     */
    public function filesWalker(
        ?string $path = null,
        ?Closure $callback = null,
        bool $read = true
    ): void {

        $callback ??= fn($file) => $this->setValue(pathinfo($file, PATHINFO_FILENAME), $file, $read);

        A::map(
            Dir::index($path, true),
            function ($item) use ($callback, $path) {
                $file = "{$path}/{$item}";

                //Check if file active
                if ($this->isActive($item) && is_file($file)) {
                    $callback($file);
                }
            }
        );
    }

    /**
     * Walk through folders recursively and set values nested in the array
     * 
     * @param string $path 
     * @param bool $read 
     * @return void 
     */
    public function deepWalker(string $path, bool $read = true): void
    {
        $this->filesWalker($path, function ($file) use ($path, $read) {
            $keychain = $this->keyFromPath($path, $file);
            $this->setValue($keychain, $file, $read);
        });
    }

    /**
     * Walk through folders recursively and set values flat in the array
     * 
     * @param string $path 
     * @param null|string $rootkey 
     * @param bool $read 
     * @param string $separator 
     * @return void 
     */
    public function flatWalker(
        string $path,
        ?string $rootkey = null,
        bool $read = true,
        string $separator = '/'
    ): void {

        if ($this->isActive($path) === false) {
            return;
        }

        $this->filesWalker(
            path: $path,
            callback: function ($file) use ($path, $rootkey, $read, $separator) {
                $key = $this->keyFromPath($path, $file, $separator);
                if ($rootkey) {
                    $key = [$rootkey, $key];
                }
                $this->setValue($key, $file, $read);
            }
        );
    }

    /**
     * Load classes from folder
     * PluginVendor\PluginName\Folder\Class
     * 
     * @param string $path 
     * @param null|string $namespace A custom namespace for classes
     * @return void 
     */
    public function loadClasses(
        string $path,
        ?string $namespace = null
    ): void {

        $namespace ??= A::join(
            A::map(
                array: Str::split($this->name, '/'),
                map: fn($item) => Str::ucfirst(Str::camel($item))
            ),
            '\\'
        );

        $this->filesWalker($path, function ($file) use ($path, $namespace) {
            $key = $namespace . '\\' . $this->keyFromPath($path, $file, '\\');
            $this->classes[$key] = $file;
        });

        $this->registerClasses();
    }

    /**
     * Sets and register defined classes
     * 
     * @param null|array $classes
     * @return void 
     */
    private function registerClasses(?array $classes = null): void
    {
        if (is_array($classes)) {
            $this->classes = $classes ?? [];
        }

        if (count($this->classes) > 0) {
            spl_autoload_register([$this, 'autoload']);
        };
    }

    /**
     * Callback from spl_autoload_register
     * 
     * @param string $className 
     * @return void 
     */
    private function autoload(string $className): void
    {
        if (array_key_exists($className, $this->classes)) {
            require_once $this->classes[$className];
            return;
        }
    }

    /**
     * Check if the array doesn't contains any closure which are not storable
     * 
     * @param mixed $data 
     * @return bool 
     */
    private function isCacheable($data): bool
    {
        if (!is_array($data)) {
            return !($data instanceof Closure);
        }
    
        foreach ($data as $value) {
            if ($value instanceof Closure || !$this->isCacheable($value)) {
                return false;
            }
        }
    
        return true;
    }
    

    /**
     * Save data, classes and to a file in the cachefolder
     * 
     * @return void 
     */
    private function saveCache(): void
    {

        //Caching disabled
        if ($this->cache === false) {
            return;
        }

        //Clean from old caches
        $cache_folder = pathinfo($this->cache_file, PATHINFO_DIRNAME);
        try {
            Dir::remove($cache_folder);
        } catch (\Throwable $th) {
            ;
        };

        Data::write($this->cache_file, [
            'classes'       => $this->classes,
            'data'          => $this->cache,
            'resistance'    => $this->cache_resistance
        ]);
    }
}
