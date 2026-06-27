import type { App } from "vue";

/**
 * Checks if the component is registered globally
 *
 * @example
 * isComponent("k-button") // => true
 * isComponent("k-unknown") // => false
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
export default function (name: string, app?: App): boolean {
	app ??= window.panel?.app;
	const components = app?._context.components ?? {};
	return Object.hasOwn(components, name) === true;
}
