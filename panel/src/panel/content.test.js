import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import Content from "./content.js";

/**
 * Creates a content module with a minimal panel mock
 *
 * @param {Object} options
 * @param {Object} options.changes - values of the changes version
 * @param {Object} options.latest - values of the latest version
 * @param {Function} options.post - mock for `panel.api.post`
 */
function factory({ changes = {}, latest = {}, post } = {}) {
	const panel = {
		api: {
			csrf: "csrf-token",
			endpoint: "/api",
			post: post ?? vi.fn(() => Promise.resolve())
		},
		dialog: {
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
					modified: null
				},
				versions: {
					changes: changes,
					latest: latest
				}
			}
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

describe("panel.content.unlock()", () => {
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

		await content.unlock();

		// the changes must be written before the lock is released,
		// otherwise a late save would rewrite the lock we just released
		expect(endpoints(panel)).toStrictEqual([
			"/pages/test/changes/save",
			"/pages/test/changes/unlock"
		]);
	});

	it("does not release the lock when the view got locked in the meantime", async () => {
		const error = new Error("The view is locked");
		error.key = "error.content.lock.notAllowed";
		error.details = {};

		const { content, panel } = factory({
			changes: { title: "New title" },
			latest: { title: "Test" },
			post: vi.fn(() => Promise.reject(error))
		});

		await expect(content.unlock()).rejects.toThrowError(
			"The changes could not be saved before unlocking"
		);

		// staying on the current view keeps both the changes and the lock,
		// so nothing is lost and the call can simply be repeated
		expect(endpoints(panel)).toStrictEqual(["/pages/test/changes/save"]);
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

		await expect(content.unlock()).rejects.toThrowError(
			"The changes could not be saved before unlocking"
		);

		expect(endpoints(panel)).toStrictEqual(["/pages/test/changes/save"]);
	});

	it("ignores failed unlock requests", async () => {
		// the lock will be released after 10 minutes anyway
		const { content } = factory({
			post: vi.fn(() => Promise.reject(new Error("Offline")))
		});

		await expect(content.unlock()).resolves.toBeUndefined();
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

describe("panel.content.unlockBeaconRequest()", () => {
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
