import type { App, Plugin, ComponentOptions, ConcreteComponent } from "vue";
import isComponent from "@/helpers/isComponent";
import { isObject } from "@/helpers/object";

/**
 * Extended component options type for the Kirby plugin API.
 * Allows string references for `mixins` and `extends`, which are resolved
 * at install time by resolveComponentMixins and resolveComponentExtension.
 */
export type Component = Omit<ComponentOptions, "mixins" | "extends"> & {
	mixins?: (string | ComponentOptions | ConcreteComponent)[];
	extends?: string | ComponentOptions | ConcreteComponent;
};

// mixins
import dialog from "@/mixins/dialog.js";
import drawer from "@/mixins/drawer.js";
import section from "@/mixins/section.js";
import Mark from "@/components/Forms/Writer/Mark";
import Node from "@/components/Forms/Writer/Node";

/**
 * Installs a plugin component
 * @since 4.0.0
 */
export function installComponent(app: App, name: string, component: Component): Component {
	// make sure component has something to show
	if (!component.template && !component.render && !component.setup && !component.extends) {
		throw new Error(
			`Plugin component "${name}" is not providing any template, render or setup method, neither is it extending a component. The component has not been registered.`,
		);
	}

	// extend the component if it defines extensions
	component = resolveComponentExtension(app, name, component);

	// remove a render method if there's a template
	component = resolveComponentRender(component);

	// add mixins
	component = resolveComponentMixins(component);

	// check if the component is replacing a core component
	if (isComponent(name, app) === true) {
		window.console.warn(`Plugin is replacing "${name}"`);
	}

	// register the component (strings in mixins/extends are resolved at this point)
	app.component(name, component as ComponentOptions);

	// return component options
	return component;
}

/**
 * Installs all components in the given object
 * @since 4.0.0
 */
export function installComponents(
	app: App,
	components?: Record<string, Component>,
): Record<string, Component> {
	if (isObject(components) === false) {
		return {};
	}

	const installed: Record<string, Component> = {};

	for (const [name, component] of Object.entries(components)) {
		try {
			installed[name] = installComponent(app, name, component);
		} catch (error) {
			window.console.warn((error as Error).message);
		}
	}

	return installed;
}

/**
 * Installs plugins
 * @since 4.0.0
 */
export function installPlugins(app: App, plugins?: Plugin[]): Plugin[] {
	if (Array.isArray(plugins) === false) {
		return [];
	}

	for (const plugin of plugins) {
		app.use(plugin);
	}

	return plugins;
}

/**
 * Resolves a component extension if defined as component name
 * @since 4.0.0
 */
export function resolveComponentExtension(app: App, name: string, component: Component): Component {
	if (typeof component?.extends !== "string") {
		return component;
	}

	// only extend if referenced component exists
	if (isComponent(component.extends, app) === false) {
		window.console.warn(
			`Problem with plugin trying to register component "${name}": cannot extend non-existent component "${component.extends}"`,
		);

		// remove the extension
		delete component.extends;

		return component;
	}

	component.extends = app.component(component.extends) as ComponentOptions;

	return component;
}

/**
 * Resolve available mixins if they are defined
 * @since 4.0.0
 */
export function resolveComponentMixins(component: Component): Component {
	if (Array.isArray(component.mixins) === false) {
		return component;
	}

	const mixins: Record<string, ComponentOptions> = {
		dialog,
		drawer,
		section,
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
					`Plugin trying to register component "${component.name}": cannot extend non-existent mixin "${mixin}"`,
				);
				return;
			}

			const extending = component.extends as ComponentOptions | undefined;

			// component inherits from a parent component:
			// make sure to only include the mixin if the parent component
			// hasn't already included it (to avoid duplicate mixins)
			if (extending) {
				const inherited = extending.mixins ?? [];
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
 */
export function resolveComponentRender(component: Component): Component {
	if (component.template) {
		delete component.render;
	}

	return component;
}

/**
 * The plugin module installs all given plugins
 * and makes them accessible at window.panel.plugins
 * @since 4.0.0
 */
export default function Plugins(
	app: App,
	plugins: Partial<{
		components: Record<string, ComponentOptions>;
		created: ((app: App) => void)[];
		icons: Record<string, string>;
		login: ComponentOptions;
		textareaButtons: Record<string, unknown>;
		writerMarks: Record<string, typeof Mark>;
		writerNodes: Record<string, typeof Node>;
		use: Plugin[];
	}> = {},
) {
	return {
		// expose helper functions for kirbyup
		resolveComponentExtension,
		resolveComponentMixins,
		resolveComponentRender,
		// defaults
		created: [],
		icons: {},
		login: undefined,
		textareaButtons: {},
		thirdParty: {},
		writerMarks: {},
		writerNodes: {},
		// registered
		...plugins,
		components: installComponents(app, plugins.components),
		use: installPlugins(app, plugins.use),
	};
}
