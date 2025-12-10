import { isObject } from "@/helpers/object.js";
import isComponent from "@/helpers/isComponent.js";

// mixins
import dialog from "@/mixins/dialog.js";
import drawer from "@/mixins/drawer.js";
import section from "@/mixins/section.js";

/**
 * Installs a plugin component
 * @since 4.0.0
 *
 * @param {Vue} app
 * @param {String} name
 * @param {Object} component
 * @returns {Object} The updated component options
 */
function installComponent(app, name, component) {
	// resolve various aspects of the component
	component = resolveComponent(app, name, component);

	// check if the component is replacing a core component
	if (isComponent(name, app) === true) {
		window.console.warn(`Plugin is replacing "${name}"`);
	}

	// register the component
	app.component(name, component);

	// return component options
	return component;
}

/**
 * Installs all components in the given object
 * @since 4.0.0
 *
 * @param {Vue} app
 * @param {Object} components
 * @returns {Object} Returns all installed components
 */
function installComponents(app, components) {
	if (isObject(components) === false) {
		return;
	}

	const installed = {};

	for (const [name, component] of Object.entries(components)) {
		try {
			installed[name] = installComponent(name, component, app);
		} catch (error) {
			window.console.warn(error.message);
		}
	}

	return installed;
}

/**
 * Installs plugins
 * @since 4.0.0
 *
 * @param {Vue} app
 * @param {Object} plugins
 * @returns {Object} Returns all installed plugins
 */
function installPlugins(app, plugins) {
	if (Array.isArray(plugins) === false) {
		return [];
	}

	for (const plugin of plugins) {
		app.use(plugin);
	}

	return plugins;
}

/**
 * Resolves various aspects of a component
 * @since 6.0.0
 *
 * @param {String} name
 * @param {Object} component
 * @param {Vue} app
 * @returns {Object} The updated component options
 */
function resolveComponent(name, component, app) {
	app ??= window.panel.app;

	// inject certain features into sections and blocks
	component = resolveComponentSpecial(component);

	// remove a render method if there’s a template
	component = resolveComponentRender(component);

	// extend the component if it defines extensions
	component = resolveComponentExtension(app, name, component);

	// add mixins
	component = resolveComponentMixins(component);

	return component;
}

/**
 * Resolves a component extension if defined as component name
 * @since 4.0.0
 *
 * @param {Vue} app
 * @param {String} name
 * @param {Object} component
 * @returns {Object} The updated/extended component options
 */
function resolveComponentExtension(app, name, component) {
	if (typeof component?.extends !== "string") {
		return component;
	}

	// only extend if referenced component exists
	if (isComponent(component.extends, app) === false) {
		window.console.warn(
			`Problem with plugin trying to register component "${name}": cannot extend non-existent component "${component.extends}"`
		);

		// remove the extension
		delete component.extends;

		return component;
	}

	component.extends = app.component(component.extends);

	return component;
}

/**
 * Resolve available mixins if they are defined
 * @since 4.0.0
 *
 * @param {Object} component
 * @returns {Object} The updated component options
 */
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

/**
 * Resolve a component's competing template/render options
 * @since 5.0.0
 *
 * @param {Object} component
 * @returns {Object} The updated component options
 */
function resolveComponentRender(component) {
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

/**
 * Injects features into components
 * @since 6.0.0
 *
 * @param {Object} component
 * @returns {Object} The updated component options
 */
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

/**
 * The plugin module installs all given plugins
 * and makes them accessible at window.panel.plugins
 * @since 4.0.0
 */
export default (app, plugins = {}) => {
	plugins = {
		// expose helper functions for kirbyup
		resolveComponent,
		// defaults
		components: {},
		created: [],
		icons: {},
		login: null,
		textareaButtons: {},
		thirdParty: {},
		use: [],
		viewButtons: {},
		writerMarks: {},
		writerNodes: {},
		// registered
		...plugins
	};

	plugins.use = installPlugins(app, plugins.use);
	plugins.components = installComponents(app, plugins.components);

	return plugins;
};
