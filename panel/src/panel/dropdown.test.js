/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import Dropdown from "./dropdown.js";
import Panel from "./panel.js";

describe.concurrent("panel.dropdown", () => {
	it("should have a default state", async () => {
		const panel = Panel.create();
		const dropdown = Dropdown(panel);

		const state = {
			abortController: null,
			component: null,
			isLoading: false,
			on: {},
			path: null,
			props: {},
			query: {},
			referrer: null,
			timestamp: null
		};

		expect(dropdown.key()).toStrictEqual("dropdown");
		expect(dropdown.state()).toStrictEqual(state);
	});

	it("should set options", async () => {
		const panel = Panel.create();
		const dropdown = Dropdown(panel);

		const options = [{ label: "A" }, { label: "B" }];

		dropdown.open({
			options: options
		});

		expect(dropdown.props.options).toStrictEqual(options);
	});
});
