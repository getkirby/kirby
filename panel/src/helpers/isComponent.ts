/**
 * Checks if the component is registered globally
 *
 * @example
 * isComponent("k-button") // => true
 * isComponent("k-unknown") // => false
 */
export default function (name: string): boolean {
	// TODO: Type properly in v6
	// @ts-expect-error VueConstructor does not fully cover the actual type
	return typeof window.Vue.options.components[name] === "function";
}
