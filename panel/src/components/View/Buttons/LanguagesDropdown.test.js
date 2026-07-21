import { describe, expect, it, vi } from "vitest";
import LanguagesDropdown from "./LanguagesDropdown.vue";

const { change } = LanguagesDropdown.methods;

/**
 * Builds a mocked component context for the `change` method
 *
 * @param {Object} options
 * @param {Boolean} options.hasDiff - whether the view has unsaved changes
 * @param {Boolean} options.updated - what `update` resolves with
 * @param {Error|null} options.updateError - error to reject `update` with
 */
function context({ hasDiff = false, updated = true, updateError = null } = {}) {
	return {
		$panel: {
			content: {
				hasDiff: vi.fn(() => hasDiff),
				unlock: vi.fn(() => Promise.resolve()),
				update: vi.fn(() =>
					updateError ? Promise.reject(updateError) : Promise.resolve(updated)
				)
			},
			error: vi.fn()
		},
		$reload: vi.fn()
	};
}

describe("LanguagesDropdown.change()", () => {
	it("does nothing when selecting the current language", async () => {
		const ctx = context({ hasDiff: true });
		await change.call(ctx, { code: "en", current: true });

		expect(ctx.$panel.content.update).not.toHaveBeenCalled();
		expect(ctx.$panel.content.unlock).not.toHaveBeenCalled();
		expect(ctx.$reload).not.toHaveBeenCalled();
	});

	it("releases the lock and reloads when switching language", async () => {
		const ctx = context({ hasDiff: false });
		await change.call(ctx, { code: "de", current: false });

		expect(ctx.$panel.content.unlock).toHaveBeenCalledOnce();
		expect(ctx.$reload).toHaveBeenCalledWith({ query: { language: "de" } });
	});

	it("does not save when there are no pending changes", async () => {
		const ctx = context({ hasDiff: false });
		await change.call(ctx, { code: "de", current: false });

		expect(ctx.$panel.content.update).not.toHaveBeenCalled();
	});

	it("persists pending changes before releasing the lock", async () => {
		const ctx = context({ hasDiff: true });
		await change.call(ctx, { code: "de", current: false });

		expect(ctx.$panel.content.update).toHaveBeenCalledOnce();

		// update must run before unlock, and unlock before the reload,
		// otherwise a late save could rewrite the lock we just released
		const update = ctx.$panel.content.update.mock.invocationCallOrder[0];
		const unlock = ctx.$panel.content.unlock.mock.invocationCallOrder[0];
		const reload = ctx.$reload.mock.invocationCallOrder[0];

		expect(update).toBeLessThan(unlock);
		expect(unlock).toBeLessThan(reload);
	});

	it("aborts the switch when the changes were not written", async () => {
		// `update` resolves without throwing when the view got locked or
		// when a newer save request took over, so the falsy result is the
		// only signal that the content did not make it to the server
		const ctx = context({ hasDiff: true, updated: false });
		await change.call(ctx, { code: "de", current: false });

		expect(ctx.$panel.content.unlock).not.toHaveBeenCalled();
		expect(ctx.$reload).not.toHaveBeenCalled();
		expect(ctx.$panel.error).not.toHaveBeenCalled();
	});

	it("aborts the switch when saving fails", async () => {
		const error = new Error("save failed");
		const ctx = context({ hasDiff: true, updateError: error });
		await change.call(ctx, { code: "de", current: false });

		expect(ctx.$panel.error).toHaveBeenCalledWith(error);
		expect(ctx.$panel.content.unlock).not.toHaveBeenCalled();
		expect(ctx.$reload).not.toHaveBeenCalled();
	});
});
