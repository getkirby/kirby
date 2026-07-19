import { describe, expect, it, vi } from "vitest";
import LanguagesDropdown from "./LanguagesDropdown.vue";

const { change } = LanguagesDropdown.methods;

/**
 * Builds a mocked component context for the `change` method
 *
 * @param {Object} options
 * @param {Error|null} options.unlockError - error to reject `unlock` with
 */
function context({ unlockError = null } = {}) {
	return {
		$panel: {
			content: {
				unlock: vi.fn(() =>
					unlockError ? Promise.reject(unlockError) : Promise.resolve()
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

	it("aborts the switch when the lock could not be released", async () => {
		// `unlock` throws when pending changes could not be written first.
		// Staying on the current language keeps both the changes and the
		// lock, so nothing is lost and the switch can simply be repeated
		const error = new Error("The changes could not be saved before unlocking");
		const ctx = context({ unlockError: error });
		await change.call(ctx, { code: "de", current: false });

		expect(ctx.$panel.error).toHaveBeenCalledWith(error);
		expect(ctx.$reload).not.toHaveBeenCalled();
	});
});
