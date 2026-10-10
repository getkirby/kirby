import { describe, expect, it, vi } from "vitest";
import isComponent from "@/helpers/isComponent";
import dialog from "@/mixins/dialog.js";
import drawer from "@/mixins/drawer.js";
import Plugins, { load } from "./plugins";

describe("panel.plugins", () => {
	describe("load()", () => {
		// each script pushes to `window.loaded`
		function script(code: string): string {
			return "data:text/javascript," + encodeURIComponent(code);
		}

		it("does nothing without scripts", async () => {
			await expect(load(undefined)).resolves.toBeUndefined();
		});

		it("loads the scripts in order", async () => {
			const loaded: string[] = [];
			Object.assign(window, { loaded });

			await load([
				script("await Promise.resolve(); window.loaded.push('a');"),
				script("window.loaded.push('b');")
			]);

			expect(loaded).toStrictEqual(["a", "b"]);
		});

		it("keeps loading after a broken script", async () => {
			const error = vi
				.spyOn(window.console, "error")
				.mockImplementation(() => {});
			const loaded: string[] = [];
			const throws = script("window.loaded.push('throws'); undefinedThing();");
			const syntax = script("window.loaded.push('syntax'); {");

			Object.assign(window, { loaded });

			await load([
				script("window.loaded.push('c');"),
				throws,
				syntax,
				script("window.loaded.push('d');")
			]);

			expect(loaded).toStrictEqual(["c", "throws", "d"]);
			expect(error).toHaveBeenCalledTimes(2);
			expect(error.mock.calls[0][0]).toBe(
				`Plugin could not be loaded: ${throws}`
			);
			expect(error.mock.calls[1][0]).toBe(
				`Plugin could not be loaded: ${syntax}`
			);

			error.mockRestore();
		});
	});

	// the resolvers are tested on the Plugins() object,
	// where kirbyup calls them
	describe("resolveComponentExtension()", () => {
		const { resolveComponentExtension } = Plugins(app);

		it("returns component unchanged when extends is not a string", () => {
			const component = { template: "<p>test</p>" };
			expect(resolveComponentExtension(app, "k-test", component)).toStrictEqual(
				component
			);
		});

		it("removes extends and warns when the referenced component is not registered", () => {
			const warn = vi
				.spyOn(window.console, "warn")
				.mockImplementation(() => {});
			const component = { extends: "k-unregistered-xyz" };

			const result = resolveComponentExtension(app, "k-custom", component);

			expect(result.extends).toBeUndefined();
			expect(warn).toHaveBeenCalledWith(
				expect.stringContaining("k-unregistered-xyz")
			);

			warn.mockRestore();
		});

		it("resolves extends to a Vue constructor when the referenced component exists", () => {
			app.component("k-plugins-test-base", { template: "<p>base</p>" });

			const component = {
				extends: "k-plugins-test-base",
				template: "<p>extended</p>"
			};
			const result = resolveComponentExtension(
				app,
				"k-plugins-test-extended",
				component
			);

			expect(typeof result.extends).not.toBe("string");
		});
	});

	describe("resolveComponentMixins()", () => {
		const { resolveComponentMixins } = Plugins(app);

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

		it("warns and removes unknown string mixins", () => {
			const warn = vi
				.spyOn(window.console, "warn")
				.mockImplementation(() => {});
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
				mixins: ["dialog"]
			};
			const result = resolveComponentMixins(component);
			expect(result.mixins).not.toContain(dialog);
		});

		it("resolves multiple mixins in a single component", () => {
			const objectMixin = { methods: { foo: () => {} } };
			const component = {
				template: "<p>test</p>",
				mixins: ["dialog", "drawer", objectMixin]
			};
			const result = resolveComponentMixins(component);
			expect(result.mixins).toContain(dialog);
			expect(result.mixins).toContain(drawer);
			expect(result.mixins).toContain(objectMixin);
		});
	});

	describe("resolveComponentRender()", () => {
		const { resolveComponentRender } = Plugins(app);

		it("sets render to null when template is present", () => {
			const render = () => null;
			const component = { template: "<p>test</p>", render };

			const result = resolveComponentRender(component);

			expect(result.render).toBeNull();
			expect(result.template).toBe("<p>test</p>");
		});

		it("keeps render when no template is present", () => {
			const render = () => null;
			const component = { render };

			const result = resolveComponentRender(component);

			expect(result.render).toBe(render);
		});
	});

	describe("Plugins()", () => {
		it("returns defaults when called with no plugins", () => {
			const plugins = Plugins(app, {});

			expect(plugins.components).toStrictEqual({});
			expect(plugins.created).toStrictEqual([]);
			expect(plugins.icons).toStrictEqual({});
			expect(plugins.textareaButtons).toStrictEqual({});
			expect(plugins.thirdParty).toStrictEqual({});
			expect(plugins.use).toStrictEqual([]);
			expect(plugins.writerMarks).toStrictEqual({});
			expect(plugins.writerNodes).toStrictEqual({});
		});

		it("merges provided icons", () => {
			const plugins = Plugins(app, { icons: { star: "<svg/>" } });
			expect(plugins.icons).toStrictEqual({ star: "<svg/>" });
		});

		it("installs components", () => {
			const component = { template: "<p>test</p>" };
			const plugins = Plugins(app, {
				components: { "k-plugins-default": component }
			});
			expect(plugins.components["k-plugins-default"]).toStrictEqual(component);
			expect(isComponent("k-plugins-default", app)).toBe(true);
		});

		it("installs a component with a render function", () => {
			const render = () => null;
			const component = { render };
			const plugins = Plugins(app, {
				components: { "k-plugins-with-render": component }
			});
			expect(plugins.components["k-plugins-with-render"]).toStrictEqual(
				component
			);
		});

		it("skips and warns for components without template, render or extends", () => {
			const warn = vi
				.spyOn(window.console, "warn")
				.mockImplementation(() => {});

			const plugins = Plugins(app, {
				components: {
					"k-valid": { template: "<p>valid</p>" },
					"k-invalid": {}
				}
			});

			expect(plugins.components["k-valid"]).toBeDefined();
			expect(plugins.components["k-invalid"]).toBeUndefined();
			expect(warn).toHaveBeenCalledWith(
				expect.stringContaining(
					`Plugin component "k-invalid" is not providing any template, render or setup method`
				)
			);

			warn.mockRestore();
		});

		it("warns when replacing a registered core component", () => {
			app.component("k-plugins-core", { template: "<p>core</p>" });
			const warn = vi
				.spyOn(window.console, "warn")
				.mockImplementation(() => {});

			Plugins(app, {
				components: { "k-plugins-core": { template: "<p>override</p>" } }
			});

			expect(warn).toHaveBeenCalledWith(
				expect.stringContaining(`Plugin is replacing "k-plugins-core"`)
			);

			warn.mockRestore();
		});

		it("installs use plugins", () => {
			const use = vi.spyOn(app, "use").mockImplementation(() => app);

			const pluginA = { install: vi.fn() };
			const pluginB = { install: vi.fn() };

			const plugins = Plugins(app, { use: [pluginA, pluginB] });

			expect(use).toHaveBeenCalledTimes(2);
			expect(use).toHaveBeenCalledWith(pluginA);
			expect(use).toHaveBeenCalledWith(pluginB);
			expect(plugins.use).toStrictEqual([pluginA, pluginB]);

			use.mockRestore();
		});

		it("merges created callbacks", () => {
			const cb = vi.fn();
			const plugins = Plugins(app, { created: [cb] });
			expect(plugins.created).toContain(cb);
		});
	});
});
