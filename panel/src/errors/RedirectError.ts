/**
 * Signals that a redirect to the given url is required.
 * Actual redirecting handled by panel.error().
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.4.0
 */
export default class RedirectError extends Error {
	url: string;

	constructor(url: string) {
		super("redirect");
		this.url = url;
	}
}
