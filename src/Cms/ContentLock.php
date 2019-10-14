<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;

/**
 * Takes care of content lock and unlock information
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class ContentLock
{
    /**
     * Lock data
     *
     * @var array
     */
    protected $data;

    /**
     * The model to manage locking/unlocking for
     *
     * @var ModelWithContent
     */
    protected $model;

    /**
     * @param \Kirby\Cms\ModelWithContent $model
     */
    public function __construct(ModelWithContent $model)
    {
        $this->model = $model;
        $this->data  = $this->kirby()->locks()->get($model);
    }

    /**
     * Sets lock with the current user
     *
     * @return bool
     */
    public function create(): bool
    {
        // check if model is already locked by another user
        if (
            isset($this->data['lock']) === true &&
            $this->data['lock']['user'] !== $this->user()->id()
        ) {
            $id = ContentLocks::id($this->model);
            throw new DuplicateException($id . ' is already locked');
        }

        $this->data['lock'] = [
            'user' => $this->user()->id(),
            'time' => time()
        ];

        return $this->kirby()->locks()->set($this->model, $this->data);
    }

    /**
     * Returns either `false` or array  with `user`, `email`,
     * `time` and `unlockable` keys
     *
     * @return array|bool
     */
    public function get()
    {
        $data = $this->data['lock'] ?? [];

        if (
            empty($data) === false &&
            $data['user'] !== $this->user()->id() &&
            $user = $this->kirby()->user($data['user'])
        ) {
            $time = (int)($data['time']);

            return [
                'user'       => $user->id(),
                'email'      => $user->email(),
                'time'       => $time,
                'unlockable' => ($time + 60) <= time()
            ];
        }

        return false;
    }

    /**
     * Returns if the model is locked by another user
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        $lock = $this->get();

        if ($lock !== false && $lock['user'] !== $this->user()->id()) {
            return true;
        }

        return false;
    }

    /**
     * Returns if the current user's lock has been removed by another user
     *
     * @return bool
     */
    public function isUnlocked(): bool
    {
        $data = $this->data['unlock'] ?? [];

        return in_array($this->user()->id(), $data) === true;
    }

    /**
     * Returns the app instance
     *
     * @return \Kirby\Cms\App
     */
    protected function kirby(): App
    {
        return $this->model->kirby();
    }

    /**
     * Removes lock of current user
     *
     * @return bool
     */
    public function remove(): bool
    {
        // if no lock exists, skip
        if (isset($this->data['lock']) === false) {
            return true;
        }

        // check if lock was set by another user
        if ($this->data['lock']['user'] !== $this->user()->id()) {
            throw new LogicException([
                'fallback' => 'The content lock can only be removed by the user who created it. Use unlock instead.',
                'httpCode' => 409
            ]);
        }

        // remove lock
        unset($this->data['lock']);

        return $this->kirby()->locks()->set($this->model, $this->data);
    }

    /**
     * Removes unlock information for current user
     *
     * @return bool
     */
    public function resolve(): bool
    {
        // if no unlocks exist, skip
        if (isset($this->data['unlock']) === false) {
            return true;
        }

        // remove user from unlock array
        $this->data['unlock'] = array_diff(
            $this->data['unlock'],
            [$this->user()->id()]
        );

        return $this->kirby()->locks()->set($this->model, $this->data);
    }

    /**
     * Removes current lock and adds lock user to unlock data
     *
     * @return bool
     */
    public function unlock(): bool
    {
        // if no lock exists, skip
        if (isset($this->data['lock']) === false) {
            return true;
        }

        // add lock user to unlocked data
        $this->data['unlock']   = $this->data['unlock'] ?? [];
        $this->data['unlock'][] = $this->data['lock']['user'];

        // remove lock
        unset($this->data['lock']);

        return $this->kirby()->locks()->set($this->model, $this->data);
    }

    /**
     * Returns currently authenticated user;
     * throws exception if none is authenticated
     *
     * @return \Kirby\Cms\User
     */
    protected function user(): User
    {
        if ($user = $this->kirby()->user()) {
            return $user;
        }

        throw new PermissionException('No user authenticated.');
    }
}
