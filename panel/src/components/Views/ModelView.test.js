import { describe, expect, it, vi } from "vitest";
import ModelView from "./ModelView.vue";

const { onBeforeUnload, onModelUpdate, unlock } = ModelView.methods;

/**
 * Builds a mocked component context
 *
 * @param {Object} options
 * @param {Boolean} options.isBusy - whether content requests are on their way
 * @param {Error|null} options.unlockError - error to reject `unlock` with
 */
function context({ isBusy = false, unlockError = null } = {}) {
	return {
		$panel: {
			content: {
				isBusy: vi.fn(() => isBusy),
				refresh: vi.fn(),
				saveLazy: {
					flush: vi.fn()
				},
				unlock: vi.fn(() =>
					unlockError ? Promise.reject(unlockError) : Promise.resolve(true)
				),
				unlockBeaconRequest: vi.fn()
			},
			error: vi.fn(),
			language: {
				code: "en"
			}
		},
		api: "/pages/test",
		isLocked: false,
		unlock
	};
}

describe("ModelView.onBeforeUnload()", () => {
	it("unlocks the view with a beacon", () => {
		const ctx = context();

		onBeforeUnload.call(ctx, { preventDefault: vi.fn() });

		expect(ctx.$panel.content.unlockBeaconRequest).toHaveBeenCalledWith({
			api: "/pages/test",
			language: "en"
		});
		expect(ctx.$panel.content.unlock).not.toHaveBeenCalled();
	});

	it("writes the scheduled save right away", () => {
		const ctx = context();

		onBeforeUnload.call(ctx, { preventDefault: vi.fn() });

		expect(ctx.$panel.content.saveLazy.flush).toHaveBeenCalledOnce();
	});

	it("warns about changes on their way and keeps the lock", () => {
		const ctx = context({ isBusy: true });
		const e = { preventDefault: vi.fn() };

		onBeforeUnload.call(ctx, e);

		// an unlock would overtake the saves, which lock the view again
		expect(e.preventDefault).toHaveBeenCalledOnce();
		expect(ctx.$panel.content.unlockBeaconRequest).not.toHaveBeenCalled();
	});

	it("does not warn when all changes have been written", () => {
		const ctx = context();
		const e = { preventDefault: vi.fn() };

		onBeforeUnload.call(ctx, e);

		expect(e.preventDefault).not.toHaveBeenCalled();
	});

	it("does not unlock a view that is locked by someone else", () => {
		const ctx = { ...context(), isLocked: true };

		onBeforeUnload.call(ctx, { preventDefault: vi.fn() });

		expect(ctx.$panel.content.unlockBeaconRequest).not.toHaveBeenCalled();
	});
});

describe("ModelView.onModelUpdate()", () => {
	it("refreshes the view once the content requests are written", () => {
		const ctx = context();

		onModelUpdate.call(ctx, { path: "/pages/test" });

		expect(ctx.$panel.content.refresh).toHaveBeenCalledOnce();
	});

	it("does not refresh for another model", () => {
		const ctx = context();

		onModelUpdate.call(ctx, { path: "/pages/other" });

		expect(ctx.$panel.content.refresh).not.toHaveBeenCalled();
	});
});

describe("ModelView.unlock()", () => {
	it("unlocks the view with a regular request", async () => {
		const ctx = context();

		await unlock.call(ctx, "/pages/old");

		expect(ctx.$panel.content.unlock).toHaveBeenCalledWith({
			api: "/pages/old",
			language: "en"
		});
		expect(ctx.$panel.content.unlockBeaconRequest).not.toHaveBeenCalled();
	});

	it("reports a scheduled save that could not be written", async () => {
		const error = new Error("Offline");
		const ctx = context({ unlockError: error });

		await unlock.call(ctx, "/pages/old");

		expect(ctx.$panel.error).toHaveBeenCalledWith(error);
	});
});
