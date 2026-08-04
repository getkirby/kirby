import { describe, expect, it, vi } from "vitest";
import LanguagesDropdown from "./LanguagesDropdown.vue";

const { change } = LanguagesDropdown.methods;

/**
 * Builds a mocked component context for the `change` method
 *
 * @param {Object} options
 * @param {Boolean} options.unlocked - what `unlock` resolves with
 * @param {Error|null} options.unlockError - error to reject `unlock` with
 */
function context({ unlocked = true, unlockError = null } = {}) {
	return {
		$panel: {
			content: {
				unlock: vi.fn(() =>
					unlockError ? Promise.reject(unlockError) : Promise.resolve(unlocked)
				)
			},
			error: vi.fn()
		},
		$reload: vi.fn()
	};
}

describe("LanguagesDropdown.change()", () => {
	it("does nothing when selecting the current language", async () => {
		const ctx = context();
		await change.call(ctx, { code: "en", current: true });

		expect(ctx.$panel.content.unlock).not.toHaveBeenCalled();
		expect(ctx.$reload).not.toHaveBeenCalled();
	});

	it("releases the lock and reloads when switching language", async () => {
		const ctx = context();
		await change.call(ctx, { code: "de", current: false });

		expect(ctx.$panel.content.unlock).toHaveBeenCalledOnce();
		expect(ctx.$reload).toHaveBeenCalledWith({ query: { language: "de" } });

		// the lock must be released before the reload, otherwise the
		// unlock request would be sent for the new language
		const unlock = ctx.$panel.content.unlock.mock.invocationCallOrder[0];
		const reload = ctx.$reload.mock.invocationCallOrder[0];

		expect(unlock).toBeLessThan(reload);
	});

	it("aborts the switch when the lock was not released", async () => {
		// `unlock` resolves with false when the pending changes could not be
		// written first, because the view got locked or a newer save took
		// over. Both are already reported, so no second error must be shown.
		// Staying on the current language keeps both the changes and the
		// lock, so nothing is lost and the switch can simply be repeated
		const ctx = context({ unlocked: false });
		await change.call(ctx, { code: "de", current: false });

		expect(ctx.$panel.error).not.toHaveBeenCalled();
		expect(ctx.$reload).not.toHaveBeenCalled();
	});

	it("aborts the switch and reports when unlocking throws", async () => {
		// genuine failures still reach the regular error handler
		const error = new Error("Offline");
		const ctx = context({ unlockError: error });
		await change.call(ctx, { code: "de", current: false });

		expect(ctx.$panel.error).toHaveBeenCalledWith(error);
		expect(ctx.$reload).not.toHaveBeenCalled();
	});
});
