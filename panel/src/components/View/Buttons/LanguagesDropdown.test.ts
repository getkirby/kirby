import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import LanguagesDropdown from "./LanguagesDropdown.vue";

/**
 * Builds a mocked $panel, whose `unlock` resolves
 * with `unlocked` or rejects with `unlockError`
 */
function panel({
	unlocked = true,
	unlockError = null
}: { unlocked?: boolean; unlockError?: Error | null } = {}) {
	return {
		content: {
			unlock: vi.fn(() =>
				unlockError ? Promise.reject(unlockError) : Promise.resolve(unlocked)
			)
		},
		error: vi.fn(),
		reload: vi.fn()
	};
}

function mount(attrs = {}, $panel = panel()) {
	return vueMount(LanguagesDropdown, {
		attrs,
		global: {
			mocks: {
				$dropdown: () => () => [],
				$panel
			}
		}
	});
}

describe("LanguagesDropdown.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-languages-dropdown");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// methods
	describe("change()", () => {
		it("does nothing when selecting the current language", async () => {
			const $panel = panel();
			await mount({}, $panel).vm.change({ code: "en", current: true });

			expect($panel.content.unlock).not.toHaveBeenCalled();
			expect($panel.reload).not.toHaveBeenCalled();
		});

		it("releases the lock and reloads when switching language", async () => {
			const $panel = panel();
			await mount({}, $panel).vm.change({ code: "de", current: false });

			expect($panel.content.unlock).toHaveBeenCalledOnce();
			expect($panel.reload).toHaveBeenCalledWith({
				query: { language: "de" }
			});

			// the lock must be released before the reload, otherwise the
			// unlock request would be sent for the new language
			const unlock = $panel.content.unlock.mock.invocationCallOrder[0];
			const reload = $panel.reload.mock.invocationCallOrder[0];

			expect(unlock).toBeLessThan(reload);
		});

		it("aborts the switch when the lock was not released", async () => {
			// `unlock` resolves with false when the pending changes could not be
			// written first, because the view got locked or a newer save took
			// over. Both are already reported, so no second error must be shown.
			// Staying on the current language keeps both the changes and the
			// lock, so nothing is lost and the switch can simply be repeated
			const $panel = panel({ unlocked: false });
			await mount({}, $panel).vm.change({ code: "de", current: false });

			expect($panel.error).not.toHaveBeenCalled();
			expect($panel.reload).not.toHaveBeenCalled();
		});

		it("aborts the switch and reports when unlocking throws", async () => {
			// genuine failures still reach the regular error handler
			const error = new Error("Offline");
			const $panel = panel({ unlockError: error });
			await mount({}, $panel).vm.change({ code: "de", current: false });

			expect($panel.error).toHaveBeenCalledWith(error);
			expect($panel.reload).not.toHaveBeenCalled();
		});
	});
});
