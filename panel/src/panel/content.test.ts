import { describe, expect, it, vi, afterEach, beforeEach } from "vitest";
import Content from "./content";

const createPanel = () => ({
	view: {
		props: {
			api: "/pages/test",
			lock: { isLocked: false, modified: new Date("2024-01-01") },
			versions: {
				latest: { title: "Original" } as Record<string, unknown>,
				changes: { title: "Original" } as Record<string, unknown>
			}
		},
		reload: vi.fn()
	},
	language: { code: "en" },
	events: { emit: vi.fn() },
	api: {
		post: vi.fn().mockResolvedValue(undefined),
		endpoint: "/api",
		csrf: "csrf-token"
	},
	dialog: {
		open: vi.fn(),
		close: vi.fn()
	},
	url: vi.fn((path: string) => `http://test.com${path}`)
});

describe("panel.content", () => {
	describe("cancelSaving()", () => {
		it("cancels the lazy save", () => {
			const panel = createPanel();
			const content = Content(panel);
			const cancel = vi.spyOn(content.saveLazy, "cancel");
			content.cancelSaving();
			expect(cancel).toHaveBeenCalledOnce();
		});

		it("aborts an ongoing save request", () => {
			const panel = createPanel();
			const content = Content(panel);
			const abort = vi.fn();
			content.saveAbortController = {
				abort,
				signal: new AbortController().signal
			} as AbortController;
			content.cancelSaving();
			expect(abort).toHaveBeenCalledOnce();
		});
	});

	describe("diff()", () => {
		it("returns empty object when changes match latest", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(content.diff()).toStrictEqual({});
		});

		it("returns changed fields", () => {
			const panel = createPanel();
			panel.view.props.versions.changes = { title: "Updated" };
			const content = Content(panel);
			expect(content.diff()).toStrictEqual({ title: "Updated" });
		});

		it("includes null for fields absent in changes", () => {
			const panel = createPanel();
			panel.view.props.versions.latest = { title: "Original", slug: "test" };
			panel.view.props.versions.changes = { title: "Original" };
			const content = Content(panel);
			expect(content.diff()).toStrictEqual({ slug: null });
		});

		it("throws when called for another view", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(() => content.diff({ api: "/pages/other" })).toThrow(
				"Cannot get changes for another view"
			);
		});
	});

	describe("discard()", () => {
		it("does nothing when already processing", async () => {
			const panel = createPanel();
			const content = Content(panel);
			content.isProcessing = true;
			await content.discard();
			expect(panel.api.post).not.toHaveBeenCalled();
		});

		it("throws when called for another view", async () => {
			const panel = createPanel();
			const content = Content(panel);
			await expect(content.discard({ api: "/pages/other" })).rejects.toThrow(
				"Cannot discard content from another view"
			);
		});

		it("throws when content is locked", async () => {
			const panel = createPanel();
			panel.view.props.lock.isLocked = true;
			const content = Content(panel);
			await expect(content.discard()).rejects.toThrow(
				"Cannot discard locked changes"
			);
		});

		it("posts discard request to API", async () => {
			const panel = createPanel();
			const content = Content(panel);
			await content.discard();
			expect(panel.api.post).toHaveBeenCalledWith(
				"/pages/test/changes/discard",
				{},
				expect.objectContaining({ headers: { "x-language": "en" } })
			);
		});

		it("resets changes to latest version after discard", async () => {
			const panel = createPanel();
			panel.view.props.versions.latest = { title: "Published" };
			panel.view.props.versions.changes = { title: "Draft" };
			const content = Content(panel);
			await content.discard();
			expect(panel.view.props.versions.changes).toStrictEqual({
				title: "Published"
			});
		});

		it("emits discard event", async () => {
			const panel = createPanel();
			const content = Content(panel);
			await content.discard();
			expect(panel.events.emit).toHaveBeenCalledWith(
				"content.discard",
				expect.objectContaining({ api: "/pages/test", language: "en" })
			);
		});

		it("resets isProcessing after completion", async () => {
			const panel = createPanel();
			const content = Content(panel);
			await content.discard();
			expect(content.isProcessing).toStrictEqual(false);
		});
	});

	describe("emit()", () => {
		it("emits event with content prefix and env", () => {
			const panel = createPanel();
			const content = Content(panel);
			content.emit("save");
			expect(panel.events.emit).toHaveBeenCalledWith("content.save", {
				api: "/pages/test",
				language: "en"
			});
		});

		it("includes additional options in event payload", () => {
			const panel = createPanel();
			const content = Content(panel);
			content.emit("publish", { values: { title: "New" } });
			expect(panel.events.emit).toHaveBeenCalledWith("content.publish", {
				values: { title: "New" },
				api: "/pages/test",
				language: "en"
			});
		});
	});

	describe("env()", () => {
		it("returns env from panel props", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(content.env()).toStrictEqual({
				api: "/pages/test",
				language: "en"
			});
		});

		it("overrides with custom env values", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(content.env({ api: "/pages/other" })).toStrictEqual({
				api: "/pages/other",
				language: "en"
			});
		});
	});

	describe("hasDiff()", () => {
		it("returns false when changes match latest", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(content.hasDiff()).toStrictEqual(false);
		});

		it("returns true when changes differ from latest", () => {
			const panel = createPanel();
			panel.view.props.versions.changes = { title: "Updated" };
			const content = Content(panel);
			expect(content.hasDiff()).toStrictEqual(true);
		});
	});

	describe("isCurrent()", () => {
		it("returns true when api and language match the current view", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(content.isCurrent()).toStrictEqual(true);
			expect(
				content.isCurrent({ api: "/pages/test", language: "en" })
			).toStrictEqual(true);
		});

		it("returns false when api differs", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(content.isCurrent({ api: "/pages/other" })).toStrictEqual(false);
		});

		it("returns false when language differs", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(content.isCurrent({ language: "de" })).toStrictEqual(false);
		});
	});

	describe("isLocked()", () => {
		it("returns false when content is not locked", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(content.isLocked()).toStrictEqual(false);
		});

		it("returns true when content is locked", () => {
			const panel = createPanel();
			panel.view.props.lock.isLocked = true;
			const content = Content(panel);
			expect(content.isLocked()).toStrictEqual(true);
		});
	});

	describe("lock()", () => {
		it("returns lock state from panel view props", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(content.lock()).toStrictEqual(panel.view.props.lock);
		});

		it("throws when accessing lock for another view", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(() => content.lock({ api: "/pages/other" })).toThrow(
				"The lock state cannot be detected for content from another view"
			);
		});
	});

	describe("lockDialog()", () => {
		it("opens the lock dialog", () => {
			const panel = createPanel();
			const content = Content(panel);
			const lock = { isLocked: true, modified: new Date() };
			content.lockDialog(lock);
			expect(panel.dialog.open).toHaveBeenCalledWith(
				expect.objectContaining({
					component: "k-lock-alert-dialog",
					props: { lock }
				})
			);
		});

		it("reloads view when dialog is closed", () => {
			const panel = createPanel();
			const content = Content(panel);
			content.lockDialog({ isLocked: true, modified: new Date() });
			const { on } = panel.dialog.open.mock.calls[0][0];
			on.close();
			expect(panel.view.reload).toHaveBeenCalledOnce();
		});
	});

	describe("merge()", () => {
		it("merges values into changes", () => {
			const panel = createPanel();
			const content = Content(panel);
			content.merge({ title: "New Title" });
			expect(panel.view.props.versions.changes).toStrictEqual({
				title: "New Title"
			});
		});

		it("returns the merged changes", () => {
			const panel = createPanel();
			const content = Content(panel);
			const result = content.merge({ title: "New Title" });
			expect(result).toStrictEqual({ title: "New Title" });
		});

		it("preserves existing changes when merging", () => {
			const panel = createPanel();
			panel.view.props.versions.changes = { title: "Draft", slug: "draft" };
			const content = Content(panel);
			content.merge({ title: "Updated" });
			expect(panel.view.props.versions.changes).toStrictEqual({
				title: "Updated",
				slug: "draft"
			});
		});

		it("throws when called for another view", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(() =>
				content.merge({ title: "New" }, { api: "/pages/other" })
			).toThrow("The content in another view cannot be merged");
		});
	});

	describe("publish()", () => {
		it("does nothing when already processing", async () => {
			const panel = createPanel();
			const content = Content(panel);
			content.isProcessing = true;
			await content.publish();
			expect(panel.api.post).not.toHaveBeenCalled();
		});

		it("throws when called for another view", async () => {
			const panel = createPanel();
			const content = Content(panel);
			await expect(
				content.publish({}, { api: "/pages/other" })
			).rejects.toThrow("Cannot publish content from another view");
		});

		it("posts publish request with merged values", async () => {
			const panel = createPanel();
			const content = Content(panel);
			await content.publish({ title: "Published" });
			expect(panel.api.post).toHaveBeenCalledWith(
				"/pages/test/changes/publish",
				expect.objectContaining({ title: "Published" }),
				expect.objectContaining({ headers: { "x-language": "en" } })
			);
		});

		it("updates latest version to current changes after publish", async () => {
			const panel = createPanel();
			panel.view.props.versions.changes = { title: "Draft" };
			const content = Content(panel);
			await content.publish();
			expect(panel.view.props.versions.latest).toStrictEqual({
				title: "Draft"
			});
		});

		it("emits publish event", async () => {
			const panel = createPanel();
			const content = Content(panel);
			await content.publish({ title: "Published" });
			expect(panel.events.emit).toHaveBeenCalledWith(
				"content.publish",
				expect.objectContaining({ api: "/pages/test" })
			);
		});

		it("resets isProcessing after completion", async () => {
			const panel = createPanel();
			const content = Content(panel);
			await content.publish();
			expect(content.isProcessing).toStrictEqual(false);
		});
	});

	describe("renewLock()", () => {
		it("replaces the lock's modified timestamp with a new date", () => {
			const panel = createPanel();
			const content = Content(panel);
			const before = panel.view.props.lock.modified;
			content.renewLock();
			expect(panel.view.props.lock.modified).not.toBe(before);
		});
	});

	describe("save()", () => {
		it("posts save request to API", async () => {
			const panel = createPanel();
			const content = Content(panel);
			await content.save({ title: "Draft" });
			expect(panel.api.post).toHaveBeenCalledWith(
				"/pages/test/changes/save",
				{ title: "Draft" },
				expect.objectContaining({ silent: true })
			);
		});

		it("renews lock after saving", async () => {
			const panel = createPanel();
			const content = Content(panel);
			const before = panel.view.props.lock.modified;
			await content.save({});
			expect(panel.view.props.lock.modified).not.toBe(before);
		});

		it("emits save event with values", async () => {
			const panel = createPanel();
			const content = Content(panel);
			await content.save({ title: "Draft" });
			expect(panel.events.emit).toHaveBeenCalledWith(
				"content.save",
				expect.objectContaining({ values: { title: "Draft" } })
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
			const panel = createPanel();
			const content = Content(panel);
			content.saveLazy({ title: "Draft" });
			expect(panel.api.post).toHaveBeenCalledTimes(1);
		});

		it("throttles rapid subsequent calls", () => {
			const panel = createPanel();
			const content = Content(panel);
			content.saveLazy({ title: "First" });
			content.saveLazy({ title: "Second" });
			content.saveLazy({ title: "Third" });
			expect(panel.api.post).toHaveBeenCalledTimes(1);
		});

		it("fires a trailing call after the throttle delay", async () => {
			const panel = createPanel();
			const content = Content(panel);
			content.saveLazy({ title: "First" });
			content.saveLazy({ title: "Second" });
			await vi.advanceTimersByTimeAsync(500);
			expect(panel.api.post).toHaveBeenCalledTimes(2);
		});
	});

	describe("unlock()", () => {
		afterEach(() => {
			vi.unstubAllGlobals();
		});

		it("uses sendBeacon with correctly constructed URL", () => {
			const panel = createPanel();
			const content = Content(panel);
			const sendBeacon = vi.fn().mockReturnValue(true);
			vi.stubGlobal("navigator", { sendBeacon });
			content.unlock();
			expect(panel.url).toHaveBeenCalledWith("/api/pages/test/changes/unlock", {
				csrf: "csrf-token",
				language: "en"
			});
			expect(sendBeacon).toHaveBeenCalledOnce();
		});

		it("falls back to regular request when sendBeacon is not queued", () => {
			const panel = createPanel();
			const content = Content(panel);
			vi.stubGlobal("navigator", {
				sendBeacon: vi.fn().mockReturnValue(false)
			});
			content.unlock();
			expect(panel.api.post).toHaveBeenCalledWith(
				"/pages/test/changes/unlock",
				{},
				expect.objectContaining({ silent: true })
			);
		});
	});

	describe("update()", () => {
		it("merges values and saves", async () => {
			const panel = createPanel();
			const content = Content(panel);
			await content.update({ title: "New Title" });
			expect(panel.api.post).toHaveBeenCalledWith(
				"/pages/test/changes/save",
				expect.objectContaining({ title: "New Title" }),
				expect.any(Object)
			);
		});
	});

	describe("updateLazy()", () => {
		beforeEach(() => {
			vi.useFakeTimers();
		});

		afterEach(() => {
			vi.useRealTimers();
		});

		it("merges values before scheduling save", () => {
			const panel = createPanel();
			const content = Content(panel);
			content.updateLazy({ title: "Lazy Title" });
			expect(panel.view.props.versions.changes).toStrictEqual({
				title: "Lazy Title"
			});
		});

		it("fires the first call immediately", () => {
			const panel = createPanel();
			const content = Content(panel);
			content.updateLazy({ title: "Draft" });
			expect(panel.api.post).toHaveBeenCalledTimes(1);
		});

		it("throttles rapid subsequent calls", () => {
			const panel = createPanel();
			const content = Content(panel);
			content.updateLazy({ title: "First" });
			content.updateLazy({ title: "Second" });
			content.updateLazy({ title: "Third" });
			expect(panel.api.post).toHaveBeenCalledTimes(1);
		});

		it("fires a trailing call after the throttle delay", async () => {
			const panel = createPanel();
			const content = Content(panel);
			content.updateLazy({ title: "First" });
			content.updateLazy({ title: "Second" });
			await vi.advanceTimersByTimeAsync(500);
			expect(panel.api.post).toHaveBeenCalledTimes(2);
		});
	});

	describe("version()", () => {
		it("returns the latest version", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(content.version("latest")).toStrictEqual(
				panel.view.props.versions.latest
			);
		});

		it("returns the changes version", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(content.version("changes")).toStrictEqual(
				panel.view.props.versions.changes
			);
		});
	});

	describe("versions()", () => {
		it("returns all versions from panel view props", () => {
			const panel = createPanel();
			const content = Content(panel);
			expect(content.versions()).toStrictEqual(panel.view.props.versions);
		});
	});
});
