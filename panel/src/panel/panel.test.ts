import {
	afterEach,
	beforeEach,
	describe,
	expect,
	it,
	onTestFinished,
	vi
} from "vitest";
import { toRaw, watch } from "vue";
import { failed } from "@test/unit/request";
import AuthError from "@/errors/AuthError";
import Panel from "./panel";

/**
 * Creates a JSON response, as the backend sends it
 */
function json(data: unknown): Response {
	return new Response(JSON.stringify(data), {
		headers: { "Content-Type": "application/json" }
	});
}

describe("panel", () => {
	beforeEach(() => {
		vi.stubGlobal("location", new URL("https://getkirby.com"));
	});

	afterEach(() => {
		document.title = "";
	});

	describe("state", () => {
		it("should have a default state", async () => {
			const panel = Panel.create(app);

			expect(panel.debug).toStrictEqual(false);
			expect(panel.direction).toStrictEqual("ltr");
			expect(panel.isLoading).toStrictEqual(false);
			expect(panel.license).toStrictEqual("missing");
			expect(panel.title).toStrictEqual("");
		});

		it("should get a full state", async () => {
			const panel = Panel.create(app);
			const state = panel.state();

			expect(state.config).toStrictEqual(panel.config);
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
			expect(state.urls).toStrictEqual(panel.urls);
			expect(state.user).toStrictEqual(panel.user.state());
		});
	});

	describe("act", () => {
		it("should do nothing without actions", async () => {
			const panel = Panel.create(app);
			const open = vi.spyOn(panel, "open");
			const reload = vi.spyOn(panel.view, "reload");

			await panel.act();

			expect(panel.notification.message).toStrictEqual(null);
			expect(open).not.toHaveBeenCalled();
			expect(reload).not.toHaveBeenCalled();
		});

		it("should emit events with the response", async () => {
			const panel = Panel.create(app);
			const response = { event: ["a", "b"] };
			const emitted: unknown[] = [];

			panel.events.on("a", (e) => emitted.push(["a", e]));
			panel.events.on("b", (e) => emitted.push(["b", e]));

			await panel.act(response);

			expect(emitted).toStrictEqual([
				["a", response],
				["b", response]
			]);
		});

		it("should open the redirect", async () => {
			const panel = Panel.create(app);
			const open = vi.spyOn(panel, "open").mockResolvedValue(undefined);
			const reload = vi.spyOn(panel.view, "reload");

			await panel.act({ redirect: "pages/a", reload: true });

			expect(open).toHaveBeenCalledWith("pages/a");
			expect(reload).not.toHaveBeenCalled();
		});

		it("should open the redirect with options", async () => {
			const panel = Panel.create(app);
			const open = vi.spyOn(panel, "open").mockResolvedValue(undefined);
			const options = { on: {} };

			await panel.act({ redirect: { url: "pages/a", options } });

			expect(open).toHaveBeenCalledWith("pages/a", options);
		});

		it("should reload the view", async () => {
			const panel = Panel.create(app);
			const reload = vi.spyOn(panel.view, "reload").mockResolvedValue(false);

			await panel.act({ reload: true });
			await panel.act({ reload: { title: "Test" } });

			expect(reload.mock.calls).toStrictEqual([[{}], [{ title: "Test" }]]);
		});

		it("should show the message", async () => {
			const panel = Panel.create(app);

			await panel.act({ message: "Synced" });

			expect(panel.notification.message).toStrictEqual("Synced");
			expect(panel.notification.theme).toStrictEqual("positive");
		});
	});

	describe("create", () => {
		it("should keep methods reactive when called on the raw instance", async () => {
			const panel = Panel.create(app);

			// modules capture the raw instance in the constructor;
			// methods called on it must still update the reactive proxy
			const raw = toRaw(panel);
			panel.get = vi.fn().mockResolvedValue({});

			const states: boolean[] = [];
			watch(
				() => panel.isLoading,
				(state) => states.push(state),
				{
					flush: "sync"
				}
			);

			await raw.open("https://getkirby.com/panel/pages/test");

			expect(states).toStrictEqual([true, false]);
		});
	});

	describe("debug", () => {
		it("should return the correct debug mode", async () => {
			const panel = Panel.create(app);

			expect(panel.debug).toStrictEqual(false);

			panel.set({
				config: {
					debug: true
				}
			});

			expect(panel.debug).toStrictEqual(true);
		});
	});

	describe("deprecated", () => {
		it("should warn about deprecations", async () => {
			const panel = Panel.create(app);
			const warn = vi.spyOn(console, "warn").mockImplementation(() => {});
			onTestFinished(() => warn.mockRestore());

			panel.deprecated("Use something else");

			expect(warn).toHaveBeenCalledWith("Deprecated: Use something else");
		});
	});

	describe("direction", () => {
		it("should return the correct direction", async () => {
			const panel = Panel.create(app);

			expect(panel.direction).toStrictEqual("ltr");

			panel.set({
				translation: {
					direction: "rtl"
				}
			});

			expect(panel.direction).toStrictEqual("rtl");
		});
	});

	describe("error", () => {
		it("should log out when the session expired", async () => {
			const panel = Panel.create(app);

			panel.set({ user: { id: "test" } });

			const url = "https://getkirby.com/api/pages/test/changes/save";

			// this is what the content autosave throws in the background
			// when the session expired while the view was open
			panel.error(
				new AuthError(
					"Unauthenticated",
					failed({ status: 401, statusText: "Unauthorized", url })
				)
			);

			expect(window.location.href).toStrictEqual("https://getkirby.com/logout");
		});

		it("should ignore aborted requests", async () => {
			const panel = Panel.create(app);

			panel.error(new DOMException("The request was aborted", "AbortError"));

			expect(panel.notification.isOpen).toStrictEqual(false);
			expect(panel.dialog.component).toStrictEqual(null);
		});

		it("should log errors in debug mode", async () => {
			const panel = Panel.create(app);
			const log = vi.spyOn(console, "error").mockImplementation(() => {});
			const error = new Error("Something went wrong");
			onTestFinished(() => log.mockRestore());

			panel.config.debug = true;
			panel.error(error, false);

			expect(log).toHaveBeenCalledWith(error);
			expect(panel.notification.isOpen).toStrictEqual(false);
		});
	});

	describe("get", () => {
		it("should return the response data", async () => {
			const panel = Panel.create(app);
			vi.stubGlobal(
				"fetch",
				vi.fn(async () => json({ title: "Test" }))
			);

			expect(await panel.get("/some/view")).toStrictEqual({ title: "Test" });
		});

		it("should return an empty object for an empty response", async () => {
			const panel = Panel.create(app);
			vi.stubGlobal(
				"fetch",
				vi.fn(async () => json(null))
			);

			expect(await panel.get("/some/view")).toStrictEqual({});
		});
	});

	describe("hasSearch", () => {
		it("should detect search types", async () => {
			const panel = Panel.create(app);

			expect(panel.hasSearch).toStrictEqual(false);

			panel.set({
				searches: { pages: { icon: "page", id: "pages", label: "Pages" } }
			});

			expect(panel.hasSearch).toStrictEqual(true);
		});
	});

	describe("open", () => {
		it("should set the state from an object", async () => {
			const panel = Panel.create(app);

			const state = await panel.open({ license: "active" });

			expect(panel.license).toStrictEqual("active");
			expect(state?.license).toStrictEqual("active");
		});

		it("should keep the listeners of a modal opened by URL", async () => {
			const panel = Panel.create(app);
			const submit = vi.fn();

			vi.spyOn(panel, "get").mockResolvedValue({
				dialog: { component: "k-text-dialog" }
			});

			await panel.open("/dialogs/test", { on: { submit } });
			await panel.dialog.submit({ title: "Test" });

			expect(submit).toHaveBeenCalledWith({ title: "Test" }, {});
		});
	});

	describe("overlays", () => {
		it("should list the open modals", async () => {
			const panel = Panel.create(app);

			expect(panel.overlays()).toStrictEqual([]);

			await panel.drawer.open({ component: "k-page-drawer" });
			await panel.dialog.open({ component: "k-text-dialog" });

			expect(panel.overlays()).toStrictEqual(["drawer", "dialog"]);
		});
	});

	describe("post", () => {
		it("should send the data and return the response data", async () => {
			const panel = Panel.create(app);
			const fetch = vi.fn<(request: Request) => Promise<Response>>(async () =>
				json({ saved: true })
			);

			vi.stubGlobal("fetch", fetch);

			expect(await panel.post("/some/view", { title: "Test" })).toStrictEqual({
				saved: true
			});

			const [request] = fetch.mock.calls[0]!;

			expect(request.method).toStrictEqual("POST");
			expect(await request.json()).toStrictEqual({ title: "Test" });
		});
	});

	describe("request", () => {
		it("should send the content language with every request", async () => {
			const panel = Panel.create(app);
			const fetch = vi.fn<(request: Request) => Promise<Response>>(
				async () =>
					new Response(JSON.stringify({}), {
						headers: { "Content-Type": "application/json" }
					})
			);

			vi.stubGlobal("fetch", fetch);

			panel.set({
				language: { code: "de", name: "Deutsch" }
			});

			await panel.request("/some/view");

			const [request] = fetch.mock.calls[0]!;

			expect(request.headers.get("x-language")).toStrictEqual("de");
		});

		it("should send the code of the current translation", async () => {
			const panel = Panel.create(app);
			const fetch = vi.fn<(request: Request) => Promise<Response>>(
				async () =>
					new Response(JSON.stringify({}), {
						headers: { "Content-Type": "application/json" }
					})
			);

			vi.stubGlobal("fetch", fetch);

			panel.set({
				translation: { code: "fr" }
			});

			await panel.request("/some/view");

			const [request] = fetch.mock.calls[0]!;

			expect(request.headers.get("x-panel-translation")).toStrictEqual("fr");
		});
	});

	describe("search", () => {
		it("should open the search dialog for the view's type", async () => {
			const panel = Panel.create(app);

			await panel.search();

			expect(panel.dialog.component).toStrictEqual("k-search-dialog");
			expect(panel.dialog.props).toStrictEqual({ type: "pages" });
		});

		it("should open the search dialog for the given type", async () => {
			const panel = Panel.create(app);

			await panel.search("users");

			expect(panel.dialog.props).toStrictEqual({ type: "users" });
		});

		it("should query the given type", async () => {
			const panel = Panel.create(app);
			const search = { results: [{ id: "a" }], pagination: {} };
			const get = vi.spyOn(panel, "get").mockResolvedValue({ search });

			const result = await panel.search("users", "Test", { limit: 5 });

			expect(get).toHaveBeenCalledWith("/search/users", {
				query: { query: "Test", limit: 5 },
				signal: expect.any(AbortSignal)
			});
			expect(result).toStrictEqual(search);
		});
	});

	describe("set", () => {
		it("should open modals", async () => {
			const panel = Panel.create(app);

			panel.set({
				dialog: { ...panel.dialog.defaults(), component: "k-text-dialog" },
				drawer: { ...panel.drawer.defaults(), component: "k-page-drawer" }
			});

			expect(panel.dialog.component).toStrictEqual("k-text-dialog");
			expect(panel.drawer.component).toStrictEqual("k-page-drawer");
		});

		it("should close modals", async () => {
			const panel = Panel.create(app);

			await panel.dialog.open({ component: "k-text-dialog" });
			await panel.drawer.open({ component: "k-page-drawer" });

			panel.set({ dialog: null, drawer: false });

			expect(panel.dialog.isOpen).toStrictEqual(false);
			expect(panel.drawer.isOpen).toStrictEqual(false);
		});

		it("should follow the redirect of a modal", async () => {
			const panel = Panel.create(app);
			const open = vi.spyOn(panel, "open").mockResolvedValue(undefined);

			panel.set({
				dialog: { ...panel.dialog.defaults(), redirect: "/pages/test" }
			});

			expect(open).toHaveBeenCalledWith("/pages/test");
		});

		it("should open the view", async () => {
			const panel = Panel.create(app);
			const push = vi
				.spyOn(window.history, "pushState")
				.mockImplementation(() => {});
			const scroll = vi.spyOn(window, "scrollTo").mockImplementation(() => {});

			onTestFinished(() => {
				push.mockRestore();
				scroll.mockRestore();
			});

			panel.set({ view: { component: "k-page-view", title: "Test" } });

			expect(panel.view.component).toStrictEqual("k-page-view");
			expect(panel.title).toStrictEqual("Test");
		});
	});

	describe("title", () => {
		it("should set the correct title without system title", async () => {
			const panel = Panel.create(app);

			panel.title = "Site";

			expect(panel.title).toStrictEqual("Site");
		});

		it("should set the correct title with system title", async () => {
			const panel = Panel.create(app);

			panel.system.title = "Kirby";
			panel.title = "Page";

			expect(panel.title).toStrictEqual("Page | Kirby");
		});

		it("should clear the title", async () => {
			const panel = Panel.create(app);
			const title = vi.spyOn(document, "title", "set");
			onTestFinished(() => title.mockRestore());

			panel.title = null;

			// browsers would turn null into "null"
			expect(title).toHaveBeenCalledWith("");
		});

		it("should only show the system title without a title", () => {
			const panel = Panel.create(app);

			panel.system.title = "Kirby";

			panel.title = null;
			expect(panel.title).toStrictEqual("Kirby");

			panel.title = "";
			expect(panel.title).toStrictEqual("Kirby");
		});
	});

	describe("url", () => {
		it("should build a URL", async () => {
			const panel = Panel.create(app);
			expect(panel.url("/path")).toStrictEqual(
				new URL("https://getkirby.com/path")
			);
		});
	});
});
