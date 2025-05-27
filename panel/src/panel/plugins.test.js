/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import Plugins from "./plugins.js";
import isComponent from "@/helpers/isComponent.js";

describe.concurrent("panel.plugins", () => {
	it("should have defaults", async () => {
		const plugins = Plugins(app);

		expect(plugins.components).toStrictEqual({});
		expect(plugins.created).toStrictEqual([]);
		expect(plugins.icons).toStrictEqual({});
		expect(plugins.login).toStrictEqual(null);
		expect(plugins.textareaButtons).toStrictEqual({});
		expect(plugins.thirdParty).toStrictEqual({});
		expect(plugins.use).toStrictEqual([]);
		expect(plugins.viewButtons).toStrictEqual({});
		expect(plugins.writerMarks).toStrictEqual({});
		expect(plugins.writerNodes).toStrictEqual({});
	});

	it("should install components", async () => {
		const component = {
			template: `<p>test</p>`
		};

		const plugins = Plugins(app, {
			components: {
				"k-test": component
			}
		});

		expect(plugins.components["k-test"]).toStrictEqual(component);
		expect(isComponent("k-test", app)).toStrictEqual(true);
	});

	it("should install plugin", async () => {
		Plugins(app, {
			use: [
				(app) => (app.config.globalProperties.$a = "A"),
				(app) => (app.config.globalProperties.$b = "B")
			]
		});

		expect(app.config.globalProperties.$a).toStrictEqual("A");
		expect(app.config.globalProperties.$b).toStrictEqual("B");
	});
});
