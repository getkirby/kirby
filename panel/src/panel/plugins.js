import Vue from "vue";
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
export const installComponent = (app, name, component) => {
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

	// extend the component if it defines extensions
	component = resolveComponentExtension(app, name, component);

	// remove a render method if there’s a template
	component = resolveComponentRender(component);

	// add mixins
	component = resolveComponentMixins(component);

	// check if the component is replacing a core component
	if (isComponent(name, app) === true) {
		window.console.warn(`Plugin is replacing "${name}"`);
	}

	// register the component
	app.component(name, component);

	// return component options
	return component;
};

/**
 * Installs all components in the given object
 * @since 4.0.0
 *
 * @param {Vue} app
 * @param {Object} components
 * @returns {Object} Returns all installed components
 */
export const installComponents = (app, components) => {
	if (isObject(components) === false) {
		return;
	}

	const installed = {};

	for (const [name, component] of Object.entries(components)) {
		try {
			installed[name] = installComponent(app, name, component);
		} catch (error) {
			window.console.warn(error.message);
		}
	}

	return installed;
};

/**
 * Installs plugins
 * @since 4.0.0
 *
 * @param {Vue} app
 * @param {Object} plugins
 * @returns {Object} Returns all installed plugins
 */
export const installPlugins = (app, plugins) => {
	if (Array.isArray(plugins) === false) {
		return [];
	}

	for (const plugin of plugins) {
		app.use(plugin);
	}

	return plugins;
};

/**
 * Resolves a component extension if defined as component name
 * @since 4.0.0
 *
 * @param {Vue} app
 * @param {String} name
 * @param {Object} component
 * @returns {Object} The updated/extended component options
 */
export const resolveComponentExtension = (app, name, component) => {
	if (typeof component?.extends !== "string") {
		return component;
	}

	// only extend if referenced component exists
	if (isComponent(component.extends, app) === false) {
		window.console.warn(
			`Problem with plugin trying to register component "${name}": cannot extend non-existent component "${component.extends}"`
		);

		// remove the extension
		component.extends = null;

		return component;
	}

	component.extends = app.component(component.extends);

	return component;
};

/**
 * Resolve available mixins if they are defined
 * @since 4.0.0
 *
 * @param {Object} component
 * @returns {Object} The updated component options
 */
export const resolveComponentMixins = (component) => {
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
			// mixin got referenced by name
			if (typeof mixin === "string" && mixins[mixin] !== undefined) {
				// component inherits from a parent component:
				// make sure to only include the mixin if the parent component
				// hasn't already included it (to avoid duplicate mixins)
				if (component.extends) {
					const extended = Vue.extend(component.extends);
					const inherited = new extended().$options.mixins ?? [];

					if (inherited.includes(mixins[mixin]) === true) {
						return;
					}
				}

				return mixins[mixin];
			}

			return mixin;
		})
		.filter((mixin) => mixin !== undefined);

	return component;
};

/**
 * Resolve a component's competing template/render options
 * @since 5.0.0
 *
 * @param {Object} component
 * @returns {Object} The updated component options
 */
export const resolveComponentRender = (component) => {
	if (component.template) {
		delete component.render;
	}

	return component;
};

/**
 * The plugin module installs all given plugins
 * and makes them accessible at window.panel.plugins
 * @since 4.0.0
 */
export default (app, plugins = {}) => {
	plugins = {
		// expose helper functions for kirbyup
		resolveComponentExtension,
		resolveComponentMixins,
		resolveComponentRender,
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
