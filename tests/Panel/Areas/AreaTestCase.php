<?php

namespace Kirby\Panel\Areas;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Filesystem\Dir;
use Kirby\Panel\Panel;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\TestCase;

abstract class AreaTestCase extends TestCase
{
    protected $app;
    protected $tmp = __DIR__ . '/tmp';

    public function app(array $params)
    {
        return $this->app = $this->app->clone($params);
    }

    public function assertErrorView(string $path, string $message)
    {
        $view = $this->view($path);
        $this->assertSame('k-error-view', $view['component']);
        $this->assertSame($message, $view['props']['error']);
    }

    public function assertFormDialog(array $dialog)
    {
        $this->assertSame('k-form-dialog', $dialog['component']);
    }

    public function assertRedirect(string $source, string $dest = '/', int $code = 302): void
    {
        $response = $this->response($source);
        $location = $response->header('Location');

        $this->assertInstanceOf('Kirby\Http\Response', $response);
        $this->assertSame($code, $response->code());
        $this->assertSame($dest, ltrim(Str::after($location, '/panel'), '/'));
    }

    public function assertRemoveDialog(array $dialog)
    {
        $this->assertSame('k-remove-dialog', $dialog['component']);
    }

    public function assertTextDialog(array $dialog)
    {
        $this->assertSame('k-text-dialog', $dialog['component']);
    }

    public function dialog(string $path)
    {
        return $this->response('dialogs/' . $path, true)['$dialog'];
    }

    public function dropdown(string $path)
    {
        return $this->response('dropdowns/' . $path, true)['$dropdown'];
    }

    public function enableMultilang(): void
    {
        $this->app([
            'options' => [
                'languages' => true
            ],
        ]);
    }

    /**
     * Fake a ready setup and install
     * the first admin user
     *
     * @return void
     */
    public function install(): void
    {
        $this->installable();
        $this->app([
            'users' => [
                [
                    'id'    => 'test',
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin',
                ]
            ]
        ]);
    }

    /**
     * Fake a ready setup
     *
     * @return void
     */
    public function installable(): void
    {
        // fake a valid server
        $_SERVER['SERVER_SOFTWARE'] = 'php';

        // installation has to be allowed
        $this->app([
            'options' => [
                'panel' => [
                    'install' => true
                ]
            ]
        ]);
    }

    public function installEditor(): void
    {
        $this->app([
            'blueprints' => [
                'users/editor' => [
                    'name' => 'editor',
                    'title' => 'Editor',
                ]
            ],
            'users' => [
                [
                    'id'    => 'test',
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin',
                ],
                [
                    'id'    => 'editor',
                    'email' => 'editor@getkirby.com',
                    'role'  => 'editor',
                ]
            ]
        ]);
    }

    public function installLanguages(): void
    {
        $this->app([
            'languages' => [
                'en' => [
                    'code'    => 'en',
                    'default' => true,
                    'name'    => 'English'
                ],
                'de' => [
                    'code'    => 'de',
                    'default' => false,
                    'name'    => 'Deutsch'
                ]
            ]
        ]);
    }

    public function login(string $user = 'test@getkirby.com'): void
    {
        $this->app->impersonate($user);
    }

    public function response(string $path = null, bool $toJson = false)
    {
        $response = Panel::router($path);

        if ($toJson === true) {
            return json_decode($response->body(), true);
        }

        return $response;
    }

    public function search(string $path)
    {
        return $this->response('search/' . $path, true)['$search'];
    }

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->tmp,
            ],
            'request' => [
                'query' => [
                    '_json' => true,
                ]
            ],
            'options' => [
                'api' => [
                    'allowImpersonation' => true
                ]
            ]
        ]);

        Dir::make($this->tmp);
    }

    public function submit(array $data)
    {
        $this->app([
            'request' => [
                'method' => 'POST',
                'body'   => $data
            ]
        ]);

        // re-authenticate after cloning the app
        $this->login();
    }

    public function tearDown(): void
    {
        // clear session file first
        $this->app->session()->destroy();

        Dir::remove($this->tmp);

        // clear blueprint cache
        Blueprint::$loaded = [];

        // clean up server software fakes
        unset($_SERVER['SERVER_SOFTWARE']);
    }

    public function view(string $path = null): array
    {
        return $this->response($path, true)['$view'];
    }
}
