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

		const expected = {
			components: {},
			created: [],
			icons: {},
			login: null,
			textareaButtons: {},
			use: [],
			thirdParty: {},
			writerMarks: {},
			writerNodes: {}
		};

		expect(plugins).toStrictEqual(expected);
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
		expect(isComponent("k-test")).true;
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
