<?php

namespace Kirby\Cms;

use Exception;

/**
 * The rules registeres validation rules
 * for the site, pages, files and users.
 *
 * Those rules are then used in various
 * places to validate input or actions, mostly
 * executed by the store.
 *
 * Outsourcing those rules helps reducing the
 * complexity of the object actions and also
 * makes it easier to test them in a functional
 * approach
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Rules
{

    /**
     * An optional object can be bound to each
     * rule. Normally the App object would
     * be bound to the rules for easier access
     * within check functions with $this.
     *
     * @var Object
     */
    protected $bind;

    /**
     * All registered rules.
     * Each rule has a name/key and a callback
     * function with any number of arguments.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Creates the rules object with all the checks
     *
     * @param array $rules
     * @param Object $bind
     */
    public function __construct(array $rules = [], $bind = null)
    {
        $this->bind  = $bind ?? $this;
        $this->rules = $rules;
    }

    /**
     * Checks if a rule is been satisfied
     *
     * ```php
     * $rules = new Rules([
     *     'validSomething' => function ($input) {
                return $input === 'validSomething';
     *      }
     * ]);
     * $rules->check('validSomething', 'invalidSomething');
     * ```
     *
     * @param  string $rule
     * @param  mixed ...$arguments
     * @return mixed
     */
    public function check(string $rule, ...$arguments)
    {
        if (isset($this->rules[$rule]) === false) {
            return true;
        }

        return $this->rules[$rule]->call($this->bind, ...$arguments);
    }

}
