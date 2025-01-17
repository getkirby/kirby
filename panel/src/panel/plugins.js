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
 * @param {Object} options
 * @returns {Object} The updated component options
 */
export const installComponent = (app, name, options) => {
	// make sure component has something to show
	if (!options.template && !options.render && !options.extends) {
		throw new Error(
			`Neither template nor render method provided. Nor extending a component when loading plugin component "${name}". The component has not been registered.`
		);
	}

	// extend the component if it defines extensions
	options = installComponentExtension(app, name, options);

	// remove a render method if there’s a template
	if (options.template) {
		options.render = null;
	}

	// add mixins
	options = installComponentMixins(options);

	// check if the component is replacing a core component
	if (isComponent(name) === true) {
		window.console.warn(`Plugin is replacing "${name}"`);
	}

	// register the component
	app.component(name, options);

	// return its options
	return options;
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

	for (const [name, options] of Object.entries(components)) {
		try {
			installed[name] = installComponent(app, name, options);
		} catch (error) {
			window.console.warn(error.message);
		}
	}

	return installed;
};

/**
 * Extends a component if it defines an extension
 * @since 4.0.0
 *
 * @param {Vue} app
 * @param {String} name
 * @param {Object} options
 * @returns {Object} The updated/extended options
 */
export const installComponentExtension = (app, name, options) => {
	if (typeof options?.extends !== "string") {
		return options;
	}

	// only extend if referenced component exists
	if (isComponent(options.extends) === false) {
		window.console.warn(
			`Problem with plugin trying to register component "${name}": cannot extend non-existent component "${options.extends}"`
		);

		// remove the extension
		options.extends = null;

		return options;
	}

	options.extends = app.options.components[options.extends].extend({
		options,
		components: {
			...app.options.components,
			...(options.components ?? {})
		}
	});

	return options;
};

/**
 * Install available mixins if they are required
 * @since 4.0.0
 *
 * @param {Object} options
 * @returns {Object} The updated options
 */
export const installComponentMixins = (options) => {
	if (Array.isArray(options.mixins) === false) {
		return options;
	}

	const mixins = {
		dialog,
		drawer,
		section
	};

	options.mixins = options.mixins
		.map((mixin) => {
			// mixin got referenced by name
			if (typeof mixin === "string" && mixins[mixin] !== undefined) {
				// component inherits from a parent component:
				// make sure to only include the mixin if the parent component
				// hasn't already included it (to avoid duplicate mixins)
				if (options.extends) {
					const inherited = new options.extends().$options.mixins ?? [];

					if (inherited.includes(mixins[mixin]) === true) {
						return;
					}
				}

				return mixins[mixin];
			}

			return mixin;
		})
		.filter((mixin) => mixin !== undefined);

	return options;
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
 * The plugin module installs all given plugins
 * and makes them accessible at window.panel.plugins
 * @since 4.0.0
 */
export default (app, plugins = {}) => {
	plugins = {
		components: {},
		created: [],
		icons: {},
		login: null,
		textareaButtons: {},
		use: [],
		thirdParty: {},
		writerMarks: {},
		writerNodes: {},
		...plugins
	};

	plugins.use = installPlugins(app, plugins.use);
	plugins.components = installComponents(app, plugins.components);

	return plugins;
};
