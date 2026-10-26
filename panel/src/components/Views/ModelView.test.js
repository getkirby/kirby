import { describe, expect, it, vi } from "vitest";
import ModelView from "./ModelView.vue";

const { onBeforeUnload, unlock } = ModelView.methods;

/**
 * Builds a mocked component context
 *
 * @param {Object} options
 * @param {Error|null} options.unlockError - error to reject `unlock` with
 */
function context({ unlockError = null } = {}) {
	return {
		$panel: {
			content: {
				isProcessing: false,
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
		isSaved: true,
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

	it("does not unlock a view that is locked by someone else", () => {
		const ctx = { ...context(), isLocked: true };

		onBeforeUnload.call(ctx, { preventDefault: vi.fn() });

		expect(ctx.$panel.content.unlockBeaconRequest).not.toHaveBeenCalled();
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
