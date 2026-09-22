import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { toRaw, watch } from "vue";
import AuthError from "@/errors/AuthError";
import Panel from "./panel";

describe("panel", () => {
	beforeEach(() => {
		vi.stubGlobal("location", new URL("https://getkirby.com"));
	});

	afterEach(() => {
		vi.unstubAllGlobals();
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

	describe("create", () => {
		it("should keep methods reactive when called on the raw instance", async () => {
			const panel = Panel.create(app);

			// modules capture the raw instance in the constructor;
			// methods called on it must still update the reactive proxy
			const raw = toRaw(panel);
			panel.get = vi.fn().mockResolvedValue({});

			const states: boolean[] = [];
			watch(() => panel.isLoading, (state) => states.push(state), {
				flush: "sync"
			});

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
				new AuthError("Unauthenticated", {
					request: new Request(url),
					response: {
						headers: new Headers(),
						json: {},
						ok: false,
						status: 401,
						statusText: "Unauthorized",
						text: "",
						url
					}
				})
			);

			expect(window.location.href).toStrictEqual("https://getkirby.com/logout");
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
