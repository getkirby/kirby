import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import Content from "./content.js";

/**
 * Creates a content module with a minimal panel mock
 *
 * @param {Object} options
 * @param {Object} options.changes - values of the changes version
 * @param {Object} options.latest - values of the latest version
 * @param {Object} options.lock - overrides for the lock state
 * @param {Function} options.post - mock for `panel.api.post`
 */
function factory({ changes = {}, latest = {}, lock = {}, post } = {}) {
	const panel = {
		api: {
			csrf: "csrf-token",
			endpoint: "/api",
			post: post ?? vi.fn(() => Promise.resolve())
		},
		dialog: {
			close: vi.fn(),
			open: vi.fn()
		},
		events: {
			emit: vi.fn()
		},
		language: {
			code: "en"
		},
		url: (url, query) => `${url}?${new URLSearchParams(query)}`,
		view: {
			props: {
				api: "/pages/test",
				lock: {
					isLocked: false,
					modified: new Date("2024-01-01"),
					...lock
				},
				versions: {
					changes: changes,
					latest: latest
				}
			},
			reload: vi.fn()
		}
	};

	return {
		content: Content(panel),
		panel: panel
	};
}

/**
 * Returns all API endpoints that have been posted to
 */
function endpoints(panel) {
	return panel.api.post.mock.calls.map((call) => call[0]);
}

/**
 * Builds an error as it is thrown for a locked view
 */
function lockError() {
	return Object.assign(new Error("The view is locked"), {
		details: {},
		key: "error.content.lock.notAllowed"
	});
}

describe("panel.content", () => {
	describe("cancelSaving()", () => {
		it("cancels the lazy save", () => {
			const { content } = factory();
			const cancel = vi.spyOn(content.saveLazy, "cancel");

			content.cancelSaving();

			expect(cancel).toHaveBeenCalledOnce();
		});

		it("aborts an ongoing save request", () => {
			const { content } = factory();
			const abort = vi.fn();

			content.saveAbortController = { abort: abort };
			content.cancelSaving();

			expect(abort).toHaveBeenCalledOnce();
		});
	});

	describe("diff()", () => {
		it("returns an empty object when changes match latest", () => {
			const { content } = factory({
				changes: { title: "Test" },
				latest: { title: "Test" }
			});

			expect(content.diff()).toStrictEqual({});
		});

		it("returns changed fields", () => {
			const { content } = factory({
				changes: { title: "Updated" },
				latest: { title: "Test" }
			});

			expect(content.diff()).toStrictEqual({ title: "Updated" });
		});

		it("includes null for fields absent in changes", () => {
			const { content } = factory({
				changes: { title: "Test" },
				latest: { title: "Test", slug: "test" }
			});

			expect(content.diff()).toStrictEqual({ slug: null });
		});

		it("throws when called for another view", () => {
			const { content } = factory();

			expect(() => content.diff({ api: "/pages/other" })).toThrowError(
				"Cannot get changes for another view"
			);
		});
	});

	describe("discard()", () => {
		it("does nothing when already processing", async () => {
			const { content, panel } = factory();

			content.isProcessing = true;
			await content.discard();

			expect(panel.api.post).not.toHaveBeenCalled();
		});

		it("throws when called for another view", async () => {
			const { content } = factory();

			await expect(content.discard({ api: "/pages/other" })).rejects.toThrowError(
				"Cannot discard content from another view"
			);
		});

		it("throws when the content is locked", async () => {
			const { content } = factory({ lock: { isLocked: true } });

			await expect(content.discard()).rejects.toThrowError(
				"Cannot discard locked changes"
			);
		});

		it("posts the discard request", async () => {
			const { content, panel } = factory();

			await content.discard();

			expect(panel.api.post).toHaveBeenCalledWith(
				"/pages/test/changes/discard",
				{},
				{ headers: { "x-language": "en" } }
			);
		});

		it("resets the changes to the latest version", async () => {
			const { content } = factory({
				changes: { title: "Draft" },
				latest: { title: "Published" }
			});

			await content.discard();

			expect(content.version("changes")).toStrictEqual({ title: "Published" });
		});

		it("emits the discard event", async () => {
			const { content, panel } = factory();

			await content.discard();

			expect(panel.events.emit).toHaveBeenCalledWith("content.discard", {
				api: "/pages/test",
				language: "en"
			});
		});

		it("opens the lock dialog when the view got locked", async () => {
			const { content, panel } = factory({
				post: vi.fn(() => Promise.reject(lockError()))
			});

			await content.discard();

			expect(panel.dialog.open).toHaveBeenCalledOnce();
		});

		it("resets isProcessing after completion", async () => {
			const { content } = factory();

			await content.discard();

			expect(content.isProcessing).toBe(false);
		});
	});

	describe("emit()", () => {
		it("emits the event with the content prefix and env", () => {
			const { content, panel } = factory();

			content.emit("save");

			expect(panel.events.emit).toHaveBeenCalledWith("content.save", {
				api: "/pages/test",
				language: "en"
			});
		});

		it("includes additional options in the payload", () => {
			const { content, panel } = factory();

			content.emit("publish", { values: { title: "New" } });

			expect(panel.events.emit).toHaveBeenCalledWith("content.publish", {
				api: "/pages/test",
				language: "en",
				values: { title: "New" }
			});
		});
	});

	describe("env()", () => {
		it("returns the env of the current view", () => {
			const { content } = factory();

			expect(content.env()).toStrictEqual({
				api: "/pages/test",
				language: "en"
			});
		});

		it("overrides with custom env values", () => {
			const { content } = factory();

			expect(content.env({ api: "/pages/other" })).toStrictEqual({
				api: "/pages/other",
				language: "en"
			});
		});
	});

	describe("hasDiff()", () => {
		it("returns false when changes match latest", () => {
			const { content } = factory({
				changes: { title: "Test" },
				latest: { title: "Test" }
			});

			expect(content.hasDiff()).toBe(false);
		});

		it("returns true when changes differ from latest", () => {
			const { content } = factory({
				changes: { title: "Updated" },
				latest: { title: "Test" }
			});

			expect(content.hasDiff()).toBe(true);
		});
	});

	describe("isCurrent()", () => {
		it("returns true when api and language match the current view", () => {
			const { content } = factory();

			expect(content.isCurrent()).toBe(true);
			expect(content.isCurrent({ api: "/pages/test", language: "en" })).toBe(
				true
			);
		});

		it("returns false when the api differs", () => {
			const { content } = factory();

			expect(content.isCurrent({ api: "/pages/other" })).toBe(false);
		});

		it("returns false when the language differs", () => {
			const { content } = factory();

			expect(content.isCurrent({ language: "de" })).toBe(false);
		});
	});

	describe("isLocked()", () => {
		it("returns false when the content is not locked", () => {
			const { content } = factory();

			expect(content.isLocked()).toBe(false);
		});

		it("returns true when the content is locked", () => {
			const { content } = factory({ lock: { isLocked: true } });

			expect(content.isLocked()).toBe(true);
		});
	});

	describe("lock()", () => {
		it("returns the lock state of the current view", () => {
			const { content, panel } = factory();

			expect(content.lock()).toStrictEqual(panel.view.props.lock);
		});

		it("throws when called for another view", () => {
			const { content } = factory();

			expect(() => content.lock({ api: "/pages/other" })).toThrowError(
				"The lock state cannot be detected for content from another view"
			);
		});
	});

	describe("lockDialog()", () => {
		it("opens the lock dialog", () => {
			const { content, panel } = factory();
			const lock = { isLocked: true, modified: new Date("2024-01-01") };

			content.lockDialog(lock);

			expect(panel.dialog.open).toHaveBeenCalledWith(
				expect.objectContaining({
					component: "k-lock-alert-dialog",
					props: { lock: lock }
				})
			);
		});

		it("reloads the view when the dialog is closed", () => {
			const { content, panel } = factory();

			content.lockDialog({ isLocked: true, modified: null });
			panel.dialog.open.mock.calls[0][0].on.close();

			expect(panel.view.reload).toHaveBeenCalledOnce();
		});
	});

	describe("merge()", () => {
		it("merges values into the changes", () => {
			const { content } = factory();

			content.merge({ title: "New title" });

			expect(content.version("changes")).toStrictEqual({ title: "New title" });
		});

		it("returns the merged changes", () => {
			const { content } = factory();

			expect(content.merge({ title: "New title" })).toStrictEqual({
				title: "New title"
			});
		});

		it("preserves existing changes", () => {
			const { content } = factory({
				changes: { title: "Draft", slug: "draft" }
			});

			content.merge({ title: "Updated" });

			expect(content.version("changes")).toStrictEqual({
				title: "Updated",
				slug: "draft"
			});
		});

		it("throws when called for another view", () => {
			const { content } = factory();

			expect(() =>
				content.merge({ title: "New" }, { api: "/pages/other" })
			).toThrowError("The content in another view cannot be merged");
		});
	});

	describe("publish()", () => {
		it("does nothing when already processing", async () => {
			const { content, panel } = factory();

			content.isProcessing = true;
			await content.publish();

			expect(panel.api.post).not.toHaveBeenCalled();
		});

		it("throws when called for another view", async () => {
			const { content } = factory();

			await expect(
				content.publish({}, { api: "/pages/other" })
			).rejects.toThrowError("Cannot publish content from another view");
		});

		it("posts the publish request with the merged values", async () => {
			const { content, panel } = factory();

			await content.publish({ title: "Published" });

			expect(panel.api.post).toHaveBeenCalledWith(
				"/pages/test/changes/publish",
				{ title: "Published" },
				{ headers: { "x-language": "en" } }
			);
		});

		it("updates the latest version to the current changes", async () => {
			const { content } = factory({ changes: { title: "Draft" } });

			await content.publish();

			expect(content.version("latest")).toStrictEqual({ title: "Draft" });
		});

		it("emits the publish event", async () => {
			const { content, panel } = factory();

			await content.publish({ title: "Published" });

			expect(panel.events.emit).toHaveBeenCalledWith(
				"content.publish",
				expect.objectContaining({ api: "/pages/test" })
			);
		});

		it("opens the lock dialog when the view got locked", async () => {
			const { content, panel } = factory({
				post: vi.fn(() => Promise.reject(lockError()))
			});

			await content.publish();

			expect(panel.dialog.open).toHaveBeenCalledOnce();
		});

		it("resets isProcessing after completion", async () => {
			const { content } = factory();

			await content.publish();

			expect(content.isProcessing).toBe(false);
		});
	});

	describe("save()", () => {
		it("posts the save request", async () => {
			const { content, panel } = factory();

			await content.save({ title: "Draft" });

			expect(panel.api.post).toHaveBeenCalledWith(
				"/pages/test/changes/save",
				{ title: "Draft" },
				expect.objectContaining({ silent: true })
			);
		});

		it("returns true when the changes have been written", async () => {
			const { content } = factory();

			await expect(content.save({ title: "Draft" })).resolves.toBe(true);
		});

		it("renews the lock timestamp", async () => {
			const { content } = factory();
			const before = content.lock().modified;

			await content.save({});

			expect(content.lock().modified).not.toBe(before);
		});

		it("emits the save event with the values", async () => {
			const { content, panel } = factory();

			await content.save({ title: "Draft" });

			expect(panel.events.emit).toHaveBeenCalledWith(
				"content.save",
				expect.objectContaining({ values: { title: "Draft" } })
			);
		});

		it("returns false and opens the lock dialog when the view got locked", async () => {
			const { content, panel } = factory({
				post: vi.fn(() => Promise.reject(lockError()))
			});

			await expect(content.save({ title: "Draft" })).resolves.toBe(false);
			expect(panel.dialog.open).toHaveBeenCalledOnce();
		});

		it("returns false when a newer save request took over", async () => {
			const error = new Error("The request was aborted");
			error.name = "AbortError";

			const { content } = factory({
				post: vi.fn(() => Promise.reject(error))
			});

			await expect(content.save({ title: "Draft" })).resolves.toBe(false);
		});

		it("rethrows any other error", async () => {
			const { content } = factory({
				post: vi.fn(() => Promise.reject(new Error("Offline")))
			});

			await expect(content.save({ title: "Draft" })).rejects.toThrowError(
				"Offline"
			);
		});
	});

	describe("saveLazy()", () => {
		beforeEach(() => {
			vi.useFakeTimers();
		});

		afterEach(() => {
			vi.useRealTimers();
		});

		it("fires the first call immediately", () => {
			const { content, panel } = factory();

			content.saveLazy({ title: "Draft" });

			expect(panel.api.post).toHaveBeenCalledOnce();
		});

		it("throttles rapid subsequent calls", () => {
			const { content, panel } = factory();

			content.saveLazy({ title: "First" });
			content.saveLazy({ title: "Second" });
			content.saveLazy({ title: "Third" });

			expect(panel.api.post).toHaveBeenCalledOnce();
		});

		it("fires a trailing call after the throttle delay", async () => {
			const { content, panel } = factory();

			content.saveLazy({ title: "First" });
			content.saveLazy({ title: "Second" });
			await vi.advanceTimersByTimeAsync(1000);

			expect(panel.api.post).toHaveBeenCalledTimes(2);
		});
	});

	describe("unlock()", () => {
		it("sends the unlock request for the current view", async () => {
			const { content, panel } = factory();

			await content.unlock();

			expect(panel.api.post).toHaveBeenCalledWith(
				"/pages/test/changes/unlock",
				{},
				{
					headers: { "x-language": "en" },
					silent: true
				}
			);
		});

		it("does not save when there are no pending changes", async () => {
			const { content, panel } = factory({
				changes: { title: "Test" },
				latest: { title: "Test" }
			});

			await content.unlock();

			expect(endpoints(panel)).toStrictEqual(["/pages/test/changes/unlock"]);
		});

		it("persists pending changes before releasing the lock", async () => {
			const { content, panel } = factory({
				changes: { title: "New title" },
				latest: { title: "Test" }
			});

			await expect(content.unlock()).resolves.toBe(true);

			// the changes must be written before the lock is released,
			// otherwise a late save would rewrite the lock we just released
			expect(endpoints(panel)).toStrictEqual([
				"/pages/test/changes/save",
				"/pages/test/changes/unlock"
			]);
		});

		it("does not release the lock when the view got locked in the meantime", async () => {
			const { content, panel } = factory({
				changes: { title: "New title" },
				latest: { title: "Test" },
				post: vi.fn(() => Promise.reject(lockError()))
			});

			await expect(content.unlock()).resolves.toBe(false);

			// staying on the current view keeps both the changes and the lock,
			// so nothing is lost and the call can simply be repeated
			expect(endpoints(panel)).toStrictEqual(["/pages/test/changes/save"]);

			// the lock dialog already reports the case, so `unlock` must not
			// raise a second error on top of it
			expect(panel.dialog.open).toHaveBeenCalledOnce();
		});

		it("does not release the lock when a newer save request took over", async () => {
			const error = new Error("The request was aborted");
			error.name = "AbortError";

			const { content, panel } = factory({
				changes: { title: "New title" },
				latest: { title: "Test" },
				post: vi.fn(() => Promise.reject(error))
			});

			// an aborted save is an internal condition that must not be
			// reported to the editor, so it aborts the unlock silently
			await expect(content.unlock()).resolves.toBe(false);

			expect(endpoints(panel)).toStrictEqual(["/pages/test/changes/save"]);
		});

		it("ignores failed unlock requests", async () => {
			// the lock will be released after 10 minutes anyway
			const { content } = factory({
				post: vi.fn(() => Promise.reject(new Error("Offline")))
			});

			await expect(content.unlock()).resolves.toBe(true);
		});

		it("skips the diff check for another view", async () => {
			// changes can only be detected for the current view
			const { content, panel } = factory({
				changes: { title: "New title" },
				latest: { title: "Test" }
			});

			await content.unlock({ api: "/pages/other" });

			expect(endpoints(panel)).toStrictEqual(["/pages/other/changes/unlock"]);
		});
	});

	describe("unlockBeaconRequest()", () => {
		let sendBeacon;

		beforeEach(() => {
			sendBeacon = vi.fn(() => true);

			Object.defineProperty(navigator, "sendBeacon", {
				configurable: true,
				value: sendBeacon,
				writable: true
			});
		});

		afterEach(() => {
			delete navigator.sendBeacon;
		});

		it("sends a beacon for the given view", () => {
			const { content, panel } = factory();

			content.unlockBeaconRequest({ api: "/pages/other", language: "de" });

			// sendBeacon cannot set custom headers, so csrf and language
			// have to be passed as query params
			expect(sendBeacon).toHaveBeenCalledWith(
				"/api/pages/other/changes/unlock?csrf=csrf-token&language=de"
			);
			expect(panel.api.post).not.toHaveBeenCalled();
		});

		it("falls back to a regular request when the beacon was not queued", () => {
			sendBeacon.mockReturnValue(false);

			const { content, panel } = factory();

			content.unlockBeaconRequest({ api: "/pages/other" });

			expect(panel.api.post).toHaveBeenCalledWith(
				"/pages/other/changes/unlock",
				{},
				{
					headers: { "x-language": "en" },
					silent: true
				}
			);
		});

		it("ignores failed fallback requests", async () => {
			sendBeacon.mockReturnValue(false);

			const { content, panel } = factory({
				post: vi.fn(() => Promise.reject(new Error("Offline")))
			});

			// the unload event must not be blocked, so the request cannot be
			// awaited. An uncaught rejection would fail the test run.
			content.unlockBeaconRequest();

			expect(panel.api.post).toHaveBeenCalledOnce();

			await vi.waitFor(() => expect(panel.api.post).toHaveBeenCalledOnce());
		});
	});

	describe("update()", () => {
		it("merges the values and saves them", async () => {
			const { content, panel } = factory({
				changes: { title: "Draft", slug: "draft" }
			});

			await expect(content.update({ title: "Updated" })).resolves.toBe(true);

			expect(panel.api.post).toHaveBeenCalledWith(
				"/pages/test/changes/save",
				{ title: "Updated", slug: "draft" },
				expect.objectContaining({ silent: true })
			);
		});
	});

	describe("version()", () => {
		it("returns the requested version", () => {
			const { content } = factory({
				changes: { title: "Draft" },
				latest: { title: "Published" }
			});

			expect(content.version("changes")).toStrictEqual({ title: "Draft" });
			expect(content.version("latest")).toStrictEqual({ title: "Published" });
		});
	});

	describe("versions()", () => {
		it("returns all versions", () => {
			const { content, panel } = factory();

			expect(content.versions()).toStrictEqual(panel.view.props.versions);
		});
	});
});
