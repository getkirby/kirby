import type { App } from "vue";

/**
 * Checks if the component is registered globally
 *
 * @example
 * isComponent("k-button") // => true
 * isComponent("k-unknown") // => false
 */
export default function (name: string, app?: App): boolean {
	// TODO: Add proper typing once we have typed Panel
	// @ts-expect-error – window.panel not yet typed
	app ??= window.panel?.app;
	const components = app?._context.components ?? {};
	return Object.hasOwn(components, name) === true;
}
