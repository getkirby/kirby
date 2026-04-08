import { describe, expect, it } from "vitest";
import Dropdown from "./dropdown";
import Panel from "./panel.js";

describe("panel.dropdown", () => {
	describe("state", () => {
		it("should have a default state", async () => {
			const panel = Panel.create(app);
			const dropdown = Dropdown(panel);

			const state = {
				component: null,
				isLoading: false,
				on: {},
				path: null,
				props: {},
				query: {},
				referrer: null,
				timestamp: null,
			};

			expect(dropdown.key()).toStrictEqual("dropdown");
			expect(dropdown.state()).toStrictEqual(state);
		});
	});

	describe("close()", () => {
		it("should reset state", async () => {
			const panel = Panel.create(app);
			const dropdown = Dropdown(panel);

			await dropdown.open({ component: "k-dropdown", props: { options: [] } });
			dropdown.close();

			expect(dropdown.component).toBeNull();
			expect(dropdown.props).toStrictEqual({});
		});

		it("should emit close event", async () => {
			const panel = Panel.create(app);
			const dropdown = Dropdown(panel);
			let emitted = false;

			await dropdown.open(
				{ component: "k-dropdown" },
				{
					on: {
						close: () => {
							emitted = true;
						},
					},
				},
			);

			dropdown.close();

			expect(emitted).toStrictEqual(true);
		});
	});

	describe("open()", () => {
		it("should set options", async () => {
			const panel = Panel.create(app);
			const dropdown = Dropdown(panel);
			const options = [{ label: "A" }, { label: "B" }];

			await dropdown.open({ props: { options } });

			expect(dropdown.props.options).toStrictEqual(options);
		});

		it("should prefix string with /dropdowns/", async () => {
			const panel = Panel.create(app);
			let openedUrl: unknown;

			panel.open = async (url: unknown) => {
				openedUrl = url;
			};

			const dropdown = Dropdown(panel);
			await dropdown.open("pages");

			expect(String(openedUrl)).toContain("/dropdowns/pages");
		});
	});

	describe("options()", () => {
		it("should return options from props", async () => {
			const panel = Panel.create(app);
			const dropdown = Dropdown(panel);
			const options = [{ label: "A" }, { label: "B" }];

			await dropdown.open({ props: { options } });

			expect(dropdown.options()).toStrictEqual(options);
		});

		it("should return empty array when props.options is not an array", async () => {
			const panel = Panel.create(app);
			const dropdown = Dropdown(panel);

			await dropdown.open({ props: { options: null } });

			expect(dropdown.options()).toStrictEqual([]);
		});

		it("should return empty array when props.options is missing", async () => {
			const panel = Panel.create(app);
			const dropdown = Dropdown(panel);

			expect(dropdown.options()).toStrictEqual([]);
		});
	});
});
