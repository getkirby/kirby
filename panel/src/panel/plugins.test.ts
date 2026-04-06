import { describe, expect, it, vi } from "vitest";
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
	resolveComponentRender,
} from "./plugins";

describe("panel.plugins", () => {
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

		it("warns and removes unknown string mixins", () => {
			const warn = vi.spyOn(window.console, "warn").mockImplementation(() => {});
			const component = { template: "<p>test</p>", mixins: ["unknown"] };
			const result = resolveComponentMixins(component);
			expect(result.mixins).not.toContain("unknown");
			expect(warn).toHaveBeenCalled();
			warn.mockRestore();
		});

		it("leaves object mixins unchanged", () => {
			const mixin = { methods: { foo: () => {} } };
			const component = { template: "<p>test</p>", mixins: [mixin] };
			const result = resolveComponentMixins(component);
			expect(result.mixins).toContain(mixin);
		});

		it("skips mixin already inherited from extends", () => {
			const parent = { mixins: [dialog] };
			const component = {
				template: "<p>test</p>",
				extends: parent,
				mixins: ["dialog"],
			};
			const result = resolveComponentMixins(component);
			expect(result.mixins).not.toContain(dialog);
		});

		it("resolves multiple mixins in a single component", () => {
			const objectMixin = { methods: { foo: () => {} } };
			const component = {
				template: "<p>test</p>",
				mixins: ["dialog", "section", objectMixin],
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
			expect(resolveComponentExtension(app, "k-test", component)).toStrictEqual(component);
		});

		it("removes extends and warns when the referenced component is not registered", () => {
			const warn = vi.spyOn(window.console, "warn").mockImplementation(() => {});
			const component = { extends: "k-unregistered-xyz" };

			const result = resolveComponentExtension(app, "k-custom", component);

			expect(result.extends).toBeUndefined();
			expect(warn).toHaveBeenCalledWith(expect.stringContaining("k-unregistered-xyz"));

			warn.mockRestore();
		});

		it("resolves extends to a Vue constructor when the referenced component exists", () => {
			app.component("k-plugins-test-base", { template: "<p>base</p>" });

			const component = {
				extends: "k-plugins-test-base",
				template: "<p>extended</p>",
			};
			const result = resolveComponentExtension(app, "k-plugins-test-extended", component);

			expect(typeof result.extends).not.toBe("string");
		});
	});

	describe("installComponent()", () => {
		it("throws when component has no template, render, or extends", () => {
			expect(() => installComponent(app, "k-empty", {})).toThrow(
				`Plugin component "k-empty" is not providing any template, render or setup method`,
			);
		});

		it("installs a component with a template", () => {
			const component = { template: "<p>test</p>" };
			const result = installComponent(app, "k-plugins-with-template", component);
			expect(result).toStrictEqual(component);
		});

		it("installs a component with a render function", () => {
			const render = () => null;
			const component = { render };
			const result = installComponent(app, "k-plugins-with-render", component);
			expect(result).toStrictEqual(component);
		});

		it("warns when replacing a registered core component", () => {
			app.component("k-plugins-core", { template: "<p>core</p>" });
			const warn = vi.spyOn(window.console, "warn").mockImplementation(() => {});

			installComponent(app, "k-plugins-core", { template: "<p>override</p>" });

			expect(warn).toHaveBeenCalledWith(
				expect.stringContaining(`Plugin is replacing "k-plugins-core"`),
			);

			warn.mockRestore();
		});
	});

	describe("installComponents()", () => {
		it("returns empty object when components is undefined", () => {
			expect(installComponents(app, undefined)).toStrictEqual({});
		});

		it("returns a map of installed components", () => {
			const component = { template: "<p>test</p>" };
			const result = installComponents(app, {
				"k-plugins-installed": component,
			});
			expect(result["k-plugins-installed"]).toStrictEqual(component);
		});

		it("skips and warns for invalid components", () => {
			const warn = vi.spyOn(window.console, "warn").mockImplementation(() => {});

			const result = installComponents(app, {
				"k-valid": { template: "<p>valid</p>" },
				"k-invalid": {},
			});

			expect(result["k-valid"]).toBeDefined();
			expect(result["k-invalid"]).toBeUndefined();
			expect(warn).toHaveBeenCalledWith(expect.stringContaining("k-invalid"));

			warn.mockRestore();
		});
	});

	describe("installPlugins()", () => {
		it("returns empty array when plugins is not an array", () => {
			expect(installPlugins(app, undefined)).toStrictEqual([]);
		});

		it("calls app.use for each plugin and returns the plugins array", () => {
			const use = vi.spyOn(app, "use").mockImplementation(() => app);

			const pluginA = { install: vi.fn() };
			const pluginB = { install: vi.fn() };

			const result = installPlugins(app, [pluginA, pluginB]);

			expect(use).toHaveBeenCalledTimes(2);
			expect(use).toHaveBeenCalledWith(pluginA);
			expect(use).toHaveBeenCalledWith(pluginB);
			expect(result).toStrictEqual([pluginA, pluginB]);

			use.mockRestore();
		});
	});

	describe("Plugins()", () => {
		it("returns defaults when called with no plugins", () => {
			const plugins = Plugins(app, {});

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
			const plugins = Plugins(app, {});

			expect(plugins.resolveComponentExtension).toBe(resolveComponentExtension);
			expect(plugins.resolveComponentMixins).toBe(resolveComponentMixins);
			expect(plugins.resolveComponentRender).toBe(resolveComponentRender);
		});

		it("merges provided icons", () => {
			const plugins = Plugins(app, { icons: { star: "<svg/>" } });
			expect(plugins.icons).toStrictEqual({ star: "<svg/>" });
		});

		it("installs components", () => {
			const component = { template: "<p>test</p>" };
			const plugins = Plugins(app, {
				components: { "k-plugins-default": component },
			});
			expect(plugins.components["k-plugins-default"]).toStrictEqual(component);
			expect(isComponent("k-plugins-default", app)).toBe(true);
		});

		it("installs use plugins", () => {
			const plugin = { install: vi.fn() };
			const use = vi.spyOn(app, "use").mockImplementation(() => app);

			Plugins(app, { use: [plugin] });

			expect(use).toHaveBeenCalledWith(plugin);

			use.mockRestore();
		});

		it("merges created callbacks", () => {
			const cb = vi.fn();
			const plugins = Plugins(app, { created: [cb] });
			expect(plugins.created).toContain(cb);
		});
	});
});
