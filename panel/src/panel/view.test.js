/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import View from "./view.js";

// dummy panel to avoid dependencies
const Panel = () => {
	return {
		dialog: {},
		drawer: {},
		open(url, options) {
			return { url, options };
		},
		url(path) {
			return {
				toString() {
					return path;
				}
			};
		}
	};
};

describe.concurrent("panel.view", () => {
	it("should have a default state", async () => {
		const view = View(Panel());

		const state = {
			abortController: null,
			breadcrumb: [],
			breadcrumbLabel: null,
			component: null,
			icon: null,
			id: null,
			isLoading: false,
			link: null,
			on: {},
			path: null,
			props: {},
			query: {},
			referrer: null,
			search: "pages",
			timestamp: null,
			title: null
		};

		expect(view.key()).toStrictEqual("view");
		expect(view.state()).toStrictEqual(state);
	});

	it("should set the panel title", async () => {
		const panel = Panel();
		const view = View(panel);

		view.set({
			title: "Site"
		});

		expect(panel.title).toStrictEqual("Site");
	});

	it("should push the state", async () => {
		const panel = Panel();
		const view = View(panel);

		view.set({
			path: "/site",
			title: "Site"
		});

		expect(window.location.pathname).toStrictEqual("/site");
	});

	it("should not set an invalid state", async () => {
		const panel = Panel();
		const view = View(panel);

		expect(view.set.bind(view)).toThrowError("Invalid view state");
	});
});
