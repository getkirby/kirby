/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import Plugins from "./plugins.js";
import Vue from "vue";
import isComponent from "@/helpers/isComponent.js";

describe.concurrent("panel.plugins", () => {
	window.Vue = Vue;

	it("should have defaults", async () => {
		const plugins = Plugins(Vue);

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

		const plugins = Plugins(Vue, {
			components: {
				"k-test": component
			}
		});

		expect(plugins.components["k-test"]).toStrictEqual(component);
		expect(isComponent("k-test")).toBe(true);
	});

	it("should install plugin", async () => {
		Plugins(Vue, {
			use: [
				(Vue) => (Vue.prototype.$a = "A"),
				(Vue) => (Vue.prototype.$b = "B")
			]
		});

		expect(Vue.prototype.$a).toStrictEqual("A");
		expect(Vue.prototype.$b).toStrictEqual("B");
	});
});
