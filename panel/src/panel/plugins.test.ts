import { describe, expect, it, vi } from "vitest";
import Vue from "vue";
import isComponent from "@/helpers/isComponent";
import dialog from "@/mixins/dialog.js";
import drawer from "@/mixins/drawer.js";
import section from "@/mixins/section.js";
import Plugins, {
	installComponent,
	installComponents,
	installPlugins,
	resolveComponentExtension,
	resolveComponentMixins,
	resolveComponentRender
} from "./plugins";

describe("panel.plugins", () => {
	// @ts-expect-error Vue 2 test setup
	window.Vue = Vue;

	describe("resolveComponentRender()", () => {
		it("removes render when template is present", () => {
			const render = () => null;
			const component = { template: "<p>test</p>", render };

			const result = resolveComponentRender(component);

			expect(result.render).toBeUndefined();
			expect(result.template).toBe("<p>test</p>");
		});

		it("keeps render when no template is present", () => {
			const render = () => null;
			const component = { render };

			const result = resolveComponentRender(component);

			expect(result.render).toBe(render);
		});
	});

	describe("resolveComponentMixins()", () => {
		it("returns component unchanged when mixins is not an array", () => {
			const component = { template: "<p>test</p>" };
			expect(resolveComponentMixins(component)).toStrictEqual(component);
		});

		it("resolves the dialog mixin by name", () => {
			const component = { template: "<p>test</p>", mixins: ["dialog"] };
			const result = resolveComponentMixins(component);
			expect(result.mixins).toContain(dialog);
		});

		it("resolves the drawer mixin by name", () => {
			const component = { template: "<p>test</p>", mixins: ["drawer"] };
			const result = resolveComponentMixins(component);
			expect(result.mixins).toContain(drawer);
		});

		it("resolves the section mixin by name", () => {
			const component = { template: "<p>test</p>", mixins: ["section"] };
			const result = resolveComponentMixins(component);
			expect(result.mixins).toContain(section);
		});

		it("leaves unknown string mixins as-is", () => {
			const component = { template: "<p>test</p>", mixins: ["unknown"] };
			const result = resolveComponentMixins(component);
			expect(result.mixins).toContain("unknown");
		});

		it("leaves object mixins unchanged", () => {
			const mixin = { methods: { foo: () => {} } };
			const component = { template: "<p>test</p>", mixins: [mixin] };
			const result = resolveComponentMixins(component);
			expect(result.mixins).toContain(mixin);
		});

		it("skips mixin already inherited from extends", () => {
			const parent = Vue.extend({ mixins: [dialog] });
			const component = {
				template: "<p>test</p>",
				extends: parent,
				mixins: ["dialog"]
			};
			const result = resolveComponentMixins(component);
			expect(result.mixins).not.toContain(dialog);
		});

		it("resolves multiple mixins in a single component", () => {
			const objectMixin = { methods: { foo: () => {} } };
			const component = {
				template: "<p>test</p>",
				mixins: ["dialog", "section", objectMixin]
			};
			const result = resolveComponentMixins(component);
			expect(result.mixins).toContain(dialog);
			expect(result.mixins).toContain(section);
			expect(result.mixins).toContain(objectMixin);
		});
	});

	describe("resolveComponentExtension()", () => {
		it("returns component unchanged when extends is not a string", () => {
			const component = { template: "<p>test</p>" };
			expect(resolveComponentExtension(Vue, "k-test", component)).toStrictEqual(
				component
			);
		});

		it("removes extends and warns when the referenced component is not registered", () => {
			const warn = vi
				.spyOn(window.console, "warn")
				.mockImplementation(() => {});
			const component = { extends: "k-unregistered-xyz" };

			const result = resolveComponentExtension(Vue, "k-custom", component);

			expect(result.extends).toBeUndefined();
			expect(warn).toHaveBeenCalledWith(
				expect.stringContaining("k-unregistered-xyz")
			);

			warn.mockRestore();
		});

		it("resolves extends to a Vue constructor when the referenced component exists", () => {
			Vue.component("k-plugins-test-base", { template: "<p>base</p>" });

			const component = {
				extends: "k-plugins-test-base",
				template: "<p>extended</p>"
			};
			const result = resolveComponentExtension(
				Vue,
				"k-plugins-test-extended",
				component
			);

			expect(typeof result.extends).not.toBe("string");
		});
	});

	describe("installComponent()", () => {
		it("throws when component has no template, render, or extends", () => {
			expect(() => installComponent(Vue, "k-empty", {})).toThrow(
				`Plugin component "k-empty" is not providing any template or render method`
			);
		});

		it("installs a component with a template", () => {
			const component = { template: "<p>test</p>" };
			const result = installComponent(
				Vue,
				"k-plugins-with-template",
				component
			);
			expect(result).toStrictEqual(component);
		});

		it("installs a component with a render function", () => {
			const render = () => null;
			const component = { render };
			const result = installComponent(Vue, "k-plugins-with-render", component);
			expect(result).toStrictEqual(component);
		});

		it("warns when replacing a registered core component", () => {
			Vue.component("k-plugins-core", { template: "<p>core</p>" });
			const warn = vi
				.spyOn(window.console, "warn")
				.mockImplementation(() => {});

			installComponent(Vue, "k-plugins-core", { template: "<p>override</p>" });

			expect(warn).toHaveBeenCalledWith(
				expect.stringContaining(`Plugin is replacing "k-plugins-core"`)
			);

			warn.mockRestore();
		});
	});

	describe("installComponents()", () => {
		it("returns empty object when components is undefined", () => {
			expect(installComponents(Vue, undefined)).toStrictEqual({});
		});

		it("returns a map of installed components", () => {
			const component = { template: "<p>test</p>" };
			const result = installComponents(Vue, {
				"k-plugins-installed": component
			});
			expect(result["k-plugins-installed"]).toStrictEqual(component);
		});

		it("skips and warns for invalid components", () => {
			const warn = vi
				.spyOn(window.console, "warn")
				.mockImplementation(() => {});

			const result = installComponents(Vue, {
				"k-valid": { template: "<p>valid</p>" },
				"k-invalid": {}
			});

			expect(result["k-valid"]).toBeDefined();
			expect(result["k-invalid"]).toBeUndefined();
			expect(warn).toHaveBeenCalledWith(expect.stringContaining("k-invalid"));

			warn.mockRestore();
		});
	});

	describe("installPlugins()", () => {
		it("returns empty array when plugins is not an array", () => {
			expect(installPlugins(Vue, undefined)).toStrictEqual([]);
		});

		it("calls app.use for each plugin and returns the plugins array", () => {
			const use = vi.spyOn(Vue, "use").mockImplementation(() => Vue);

			const pluginA = (V: typeof Vue) => (V.prototype.$pluginA = true);
			const pluginB = (V: typeof Vue) => (V.prototype.$pluginB = true);

			const result = installPlugins(Vue, [pluginA, pluginB]);

			expect(use).toHaveBeenCalledTimes(2);
			expect(use).toHaveBeenCalledWith(pluginA);
			expect(use).toHaveBeenCalledWith(pluginB);
			expect(result).toStrictEqual([pluginA, pluginB]);

			use.mockRestore();
		});
	});

	describe("Plugins()", () => {
		it("returns defaults when called with no plugins", () => {
			const plugins = Plugins(Vue, {});

			expect(plugins.created).toStrictEqual([]);
			expect(plugins.icons).toStrictEqual({});
			expect(plugins.login).toBeUndefined();
			expect(plugins.textareaButtons).toStrictEqual({});
			expect(plugins.thirdParty).toStrictEqual({});
			expect(plugins.use).toStrictEqual([]);
			expect(plugins.writerMarks).toStrictEqual({});
			expect(plugins.writerNodes).toStrictEqual({});
		});

		it("exposes helper functions", () => {
			const plugins = Plugins(Vue, {});

			expect(plugins.resolveComponentExtension).toBe(resolveComponentExtension);
			expect(plugins.resolveComponentMixins).toBe(resolveComponentMixins);
			expect(plugins.resolveComponentRender).toBe(resolveComponentRender);
		});

		it("merges provided icons", () => {
			const plugins = Plugins(Vue, { icons: { star: "<svg/>" } });
			expect(plugins.icons).toStrictEqual({ star: "<svg/>" });
		});

		it("installs components", () => {
			const component = { template: "<p>test</p>" };
			const plugins = Plugins(Vue, {
				components: { "k-plugins-default": component }
			});
			expect(plugins.components["k-plugins-default"]).toStrictEqual(component);
			expect(isComponent("k-plugins-default")).toBe(true);
		});

		it("installs use plugins", () => {
			const plugin = vi.fn();
			const use = vi.spyOn(Vue, "use").mockImplementation(() => Vue);

			Plugins(Vue, { use: [plugin] });

			expect(use).toHaveBeenCalledWith(plugin);

			use.mockRestore();
		});

		it("merges created callbacks", () => {
			const cb = vi.fn();
			const plugins = Plugins(Vue, { created: [cb] });
			expect(plugins.created).toContain(cb);
		});
	});
});
