/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import Panel from "./panel.js";
import Vue from "vue";

describe.concurrent("panel", () => {
	window.location = new URL("https://getkirby.com");

	it("should have a default state", async () => {
		const panel = Panel.create(Vue);

		expect(panel.debug).toStrictEqual(false);
		expect(panel.direction).toStrictEqual("ltr");
		expect(panel.isLoading).toStrictEqual(false);
		expect(panel.license).toStrictEqual("missing");
		expect(panel.title).toStrictEqual("");
	});

	it("should get a full state", async () => {
		const panel = Panel.create(Vue);
		const state = panel.state();

		expect(state.config).toStrictEqual({});
		expect(state.language).toStrictEqual(panel.language.state());
		expect(state.languages).toStrictEqual([]);
		expect(state.license).toStrictEqual("missing");
		expect(state.menu).toStrictEqual(panel.menu.state());
		expect(state.multilang).toStrictEqual(false);
		expect(state.notification).toStrictEqual(panel.notification.state());
		expect(state.permissions).toStrictEqual({});
		expect(state.searches).toStrictEqual({});
		expect(state.system).toStrictEqual(panel.system.state());
		expect(state.translation).toStrictEqual(panel.translation.state());
		expect(state.urls).toStrictEqual({});
		expect(state.user).toStrictEqual(panel.user.state());
	});

	it("should return the correct debug mode", async () => {
		const panel = Panel.create(Vue);

		expect(panel.debug).toStrictEqual(false);

		panel.set({
			config: {
				debug: true
			}
		});

		expect(panel.debug).toStrictEqual(true);
	});

	it("should return the correct direction", async () => {
		const panel = Panel.create(Vue);

		expect(panel.direction).toStrictEqual("ltr");

		panel.set({
			translation: {
				direction: "rtl"
			}
		});

		expect(panel.direction).toStrictEqual("rtl");
	});

	it("should set the correct title without system title", async () => {
		const panel = Panel.create(Vue);

		// // when the sytem title is empty
		panel.title = "Site";

		expect(panel.title).toStrictEqual("Site");
	});

	it("should set the correct title with system title", async () => {
		const panel = Panel.create(Vue);

		// set a system title
		panel.system.title = "Kirby";
		panel.title = "Page";

		expect(panel.title).toStrictEqual("Page | Kirby");
	});

	it("should build a URL", async () => {
		const panel = Panel.create(Vue);
		expect(panel.url("/path")).toStrictEqual(
			new URL("https://getkirby.com/path")
		);
	});
});
