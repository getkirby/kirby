import { isObject } from "@/helpers/object.js";
import isComponent from "@/helpers/isComponent.js";

// mixins
import dialog from "@/mixins/dialog.js";
import drawer from "@/mixins/drawer.js";
import section from "@/mixins/section.js";

/**
 * The plugin module installs all given plugins
 * and makes them accessible at window.panel.plugins
 */
export default (panel) => {
	const plugins = window.panel.plugins ?? {};

	function resolveComponent(name, component) {
		component = resolveComponentSpecial(component);
		component = resolveComponentRender(name, component);
		component = resolveComponentExtension(name, component);
		component = resolveComponentMixins(component);
		return component;
	}

	function resolveComponentExtension(name, component) {
		if (typeof component?.extends !== "string") {
			return component;
		}

		// only extend if referenced component exists
		if (isComponent(component.extends, panel.app) === false) {
			window.console.warn(
				`Problem with plugin trying to register component "${name}": cannot extend non-existent component "${component.extends}"`
			);

			delete component.extends;
			return component;
		}

		return { ...component, extends: panel.app.component(component.extends) };
	}

	function resolveComponentMixins(component) {
		if (Array.isArray(component.mixins) === false) {
			return component;
		}

		const mixins = {
			dialog,
			drawer,
			section
		};

		component.mixins = component.mixins
			.map((mixin) => {
				// mixin is already an object
				if (typeof mixin !== "string") {
					return mixin;
				}

				// referenced mixin doesn't exist
				if (mixins[mixin] === undefined) {
					window.console.warn(
						`Plugin trying to register component "${component.name}": cannot extend non-existent mixin "${mixin}"`
					);
					return;
				}

				// component inherits from a parent component:
				// make sure to only include the mixin if the parent component
				// hasn't already included it (to avoid duplicate mixins)
				if (component.extends) {
					const inherited = component.extends.mixins ?? [];
					if (inherited.includes(mixins[mixin]) === true) {
						return;
					}
				}

				return mixins[mixin];
			})
			.filter((mixin) => mixin !== undefined);

		return component;
	}

	function resolveComponentRender(name, component) {
		// make sure component has something to show
		if (
			!component.template &&
			!component.render &&
			!component.setup &&
			!component.extends
		) {
			throw new Error(
				`Plugin component "${name}" is not providing any template, render or setup method, neither is it extending a component. The component has not been registered.`
			);
		}

		if (component.template) {
			delete component.render;
		}

		return component;
	}

	function resolveComponentSpecial(component) {
		// Blocks
		if (component.$isKirbyBlock) {
			if (typeof component === "string") {
				component = { template: component };
			}

			return {
				...component,
				extends: "k-block-type-default"
			};
		}

		// Sections
		if (component.$isKirbySection) {
			return {
				...component,
				mixins: ["section", ...(component.mixins ?? [])]
			};
		}

		return component;
	}

	// install all app.use plugins
	if (Array.isArray(plugins.use) === true) {
		for (const plugin of plugins.use) {
			panel.app.use(plugin);
		}
	}

	// install all Vue components
	if (isObject(plugins.components) === true) {
		for (const [name, component] of Object.entries(plugins.components)) {
			try {
				if (isComponent(name, panel.app) === true) {
					window.console.warn(`Plugin is replacing "${name}"`);
				}

				const resolved = resolveComponent(name, component);
				panel.app.component(name, resolved);
			} catch (error) {
				window.console.warn(error.message);
			}
		}
	}

	return {
		get created() {
			return plugins.created ?? [];
		},

		get icons() {
			return plugins.login;
		},

		get login() {
			return plugins.login;
		},

		resolveComponent,

		get writerNodes() {
			return plugins.textareaButtons ?? {};
		},

		get writerMarks() {
			return plugins.writerMarks ?? {};
		},

		get writerNodes() {
			return plugins.writerNodes ?? {};
		}
	};
};
