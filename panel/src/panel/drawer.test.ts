import { describe, expect, it, vi } from "vitest";
import Drawer from "./drawer";
import Panel from "./panel";

/**
 * Renders the markup of an open modal in its portal
 */
function render(key: string) {
	const portal = document.createElement("div");
	const form = document.createElement("form");
	const input = document.createElement("input");
	const requestSubmit = vi.fn();

	portal.classList.add(`k-${key}-portal`);
	form.classList.add(`k-${key}`);

	Object.defineProperty(form, "requestSubmit", {
		configurable: true,
		value: requestSubmit
	});

	form.append(input);
	portal.append(form);
	document.body.append(portal);

	return { form, input, portal, requestSubmit };
}

describe("panel.drawer", () => {
	describe("state", () => {
		it("should have a default state", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);

			const state = {
				component: null,
				id: null,
				isLoading: false,
				on: {},
				path: null,
				props: {},
				query: {},
				referrer: null,
				timestamp: null
			};

			expect(drawer.key()).toStrictEqual("drawer");
			expect(drawer.state()).toStrictEqual(state);
		});
	});

	describe("breadcrumb", () => {
		it("should be empty by default", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);

			expect(drawer.breadcrumb).toStrictEqual([]);
		});

		it("should reflect history milestones", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);

			await drawer.open({ component: "k-page-drawer" });

			expect(drawer.breadcrumb).toHaveLength(1);
		});
	});

	describe("icon", () => {
		it("should default to box", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);

			expect(drawer.icon).toStrictEqual("box");
		});

		it("should return the icon from props", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);

			await drawer.open({
				component: "k-page-drawer",
				props: { icon: "page", value: {} }
			});

			expect(drawer.icon).toStrictEqual("page");
		});
	});

	describe("open()", () => {
		it("should open a drawer component", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);

			await drawer.open({ component: "k-page-drawer" });

			expect(drawer.isOpen).toStrictEqual(true);
			expect(drawer.component).toStrictEqual("k-page-drawer");
		});

		it("should add to history when a component is given", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);

			await drawer.open({ component: "k-page-drawer" });

			expect(drawer.history.isEmpty()).toStrictEqual(false);
			expect(drawer.id).toBeTypeOf("string");
		});

		it("should not mark as open when no component is given", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);

			await drawer.open({});

			expect(drawer.isOpen).toStrictEqual(false);
		});

		it("should track multiple drawers in history", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);

			await drawer.open({ component: "k-first-drawer" });
			await drawer.open({ component: "k-second-drawer" });

			expect(drawer.history.milestones).toHaveLength(2);
		});

		it("should replace the current history entry when replace is true", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);

			await drawer.open({ component: "k-first-drawer" });
			await drawer.open({ component: "k-second-drawer", replace: true });

			expect(drawer.history.milestones).toHaveLength(1);
			expect(drawer.component).toStrictEqual("k-second-drawer");
		});

		it("should prefix string paths with /drawers/", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);
			const load = vi.spyOn(drawer, "load").mockResolvedValue(drawer.state());

			await drawer.open("some/drawer");

			expect(load).toHaveBeenCalledWith(
				"/drawers/some/drawer",
				expect.anything()
			);

			load.mockRestore();
		});

		it("should handle a drawer object with a url property", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);
			const load = vi.spyOn(drawer, "load").mockResolvedValue(drawer.state());

			await drawer.open({ url: "some/drawer" });

			expect(load).toHaveBeenCalledWith(
				"/drawers/some/drawer",
				expect.anything()
			);

			load.mockRestore();
		});

		it("should pass options when using a drawer object with a url property", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);
			const load = vi.spyOn(drawer, "load").mockResolvedValue(drawer.state());
			const onSubmit = vi.fn();

			await drawer.open({ url: "some/drawer", on: { submit: onSubmit } });

			expect(load).toHaveBeenCalledWith(
				"/drawers/some/drawer",
				expect.objectContaining({ on: { submit: onSubmit } })
			);

			load.mockRestore();
		});

		it("should open the first tab by default", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);

			await drawer.open({
				component: "k-page-drawer",
				props: {
					value: {},
					tabs: {
						content: { fields: { title: {} } },
						seo: { fields: { metaTitle: {} } }
					}
				}
			});

			expect(drawer.props.tab).toStrictEqual("content");
		});

		it("should open the given tab", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);

			await drawer.open({
				component: "k-page-drawer",
				tab: "seo",
				props: {
					value: {},
					tabs: {
						content: { fields: { title: {} } },
						seo: { fields: { metaTitle: {} } }
					}
				}
			});

			expect(drawer.props.tab).toStrictEqual("seo");
		});
	});

	describe("tab()", () => {
		it("should switch to a tab", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);

			await drawer.open({
				component: "k-page-drawer",
				props: {
					value: {},
					tabs: {
						content: { fields: { title: {} } },
						seo: { fields: { metaTitle: {} } }
					}
				}
			});

			drawer.tab("seo");

			expect(drawer.props.tab).toStrictEqual("seo");
			expect(drawer.props.fields).toStrictEqual({ metaTitle: {} });
		});

		it("should emit a tab event", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);
			let tabbed: unknown = null;

			await drawer.open({
				component: "k-page-drawer",
				on: {
					tab(value) {
						tabbed = value;
					}
				},
				props: {
					value: {},
					tabs: {
						content: { fields: {} },
						seo: { fields: {} }
					}
				}
			});

			drawer.tab("seo");

			expect(tabbed).toStrictEqual("seo");
		});

		it("should do nothing when no tabs are defined", async () => {
			const panel = Panel.create(app);
			const drawer = Drawer(panel);

			await drawer.open({ component: "k-page-drawer" });

			drawer.tab();

			expect(drawer.props.tab).toBeUndefined();
		});
	});

	describe("drawer.save event", () => {
		it("should submit the drawer and prevent default", async () => {
			const panel = Panel.create(app);
			const event = { preventDefault: vi.fn() };
			let submitted = false;

			await panel.drawer.open({
				component: "k-page-drawer",
				on: {
					submit() {
						submitted = true;
					}
				}
			});

			panel.events.emit("drawer.save", event);

			expect(submitted).toStrictEqual(true);
			expect(event.preventDefault).toHaveBeenCalled();
		});

		it("should submit the active drawer form when the focus is outside of it", () => {
			document.body.innerHTML = "";

			const panel = Panel.create(app);
			const { requestSubmit } = render("drawer");
			const preventDefault = vi.fn();
			const submit = vi.spyOn(panel.drawer, "submit");

			panel.events.emit("drawer.save", {
				preventDefault,
				target: document.body
			});

			expect(preventDefault).toHaveBeenCalledOnce();
			expect(requestSubmit).toHaveBeenCalledOnce();
			expect(submit).not.toHaveBeenCalled();
		});

		it("should fall back to direct submit without a drawer form", () => {
			document.body.innerHTML = "";

			const panel = Panel.create(app);
			const preventDefault = vi.fn();
			const submit = vi.spyOn(panel.drawer, "submit");

			panel.events.emit("drawer.save", { preventDefault });

			expect(preventDefault).toHaveBeenCalledOnce();
			expect(submit).toHaveBeenCalledOnce();
		});
	});
});
