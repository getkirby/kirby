import { describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import { defineComponent, type PropType } from "vue";
import Button from "@/components/Navigation/Button.vue";
import LanguagesDropdown from "./LanguagesDropdown.vue";

type Language = { code: string; current: boolean; text: string };

const languages: Language[] = [
	{ code: "en", current: true, text: "English" },
	{ code: "de", current: false, text: "Deutsch" }
];

/**
 * Renders the `item` slot for each option, like k-dropdown
 */
const Dropdown = defineComponent({
	props: { options: Array as PropType<Language[]> },
	render() {
		return this.options?.map((item, index) =>
			this.$slots.item?.({ item, index })
		);
	}
});

/**
 * Builds a mocked $panel, whose `unlock` resolves
 * with `unlocked` or rejects with `unlockError`
 */
function panel({
	locked = false,
	unlocked = true,
	unlockError = null
}: {
	locked?: boolean;
	unlocked?: boolean;
	unlockError?: Error | null;
} = {}) {
	return {
		content: {
			isLocked: () => locked,
			unlock: vi.fn(() =>
				unlockError ? Promise.reject(unlockError) : Promise.resolve(unlocked)
			)
		},
		error: vi.fn(),
		reload: vi.fn()
	};
}

function mount(attrs = {}, $panel = panel(), $dropdown = () => languages) {
	return vueMount(LanguagesDropdown, {
		attrs,
		global: {
			components: { "k-dropdown": Dropdown },
			mocks: { $dropdown, $panel }
		}
	});
}

/**
 * Mounts with the real k-button, which renders the badge
 */
function button(props = {}, $panel = panel()) {
	return vueMount(LanguagesDropdown, {
		props,
		global: {
			components: { "k-button": Button, "k-dropdown": Dropdown },
			mocks: { $dropdown: () => languages, $panel }
		}
	});
}

/**
 * Clicks the dropdown item of a language
 */
async function select(wrapper: ReturnType<typeof mount>, code: string) {
	await wrapper
		.find(`.k-languages-dropdown-item[code="${code}"]`)
		.trigger("click");
	await flushPromises();
}

describe("k-languages-dropdown", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-languages-dropdown");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// props
	describe("options prop", () => {
		it("renders an item for each language", () => {
			const $dropdown = vi.fn(() => languages);
			const wrapper = mount({ options: "languages" }, panel(), $dropdown);
			const codes = wrapper
				.findAll(".k-languages-dropdown-item-code")
				.map((code) => code.text());

			expect($dropdown).toHaveBeenCalledWith("languages");
			expect(codes).toStrictEqual(["EN", "DE"]);
		});
	});

	// computed
	describe("changesBadge computed", () => {
		it("is red when the content is locked", () => {
			const wrapper = button({ hasDiff: true }, panel({ locked: true }));
			const badge = wrapper.find(".k-button-badge");

			expect(badge.attributes("data-theme")).toBe("red");
		});

		it("is orange for unsaved changes", () => {
			const wrapper = button({ hasDiff: true });
			const badge = wrapper.find(".k-button-badge");

			expect(badge.attributes("data-theme")).toBe("orange");
		});

		it("is not shown without changes", () => {
			const wrapper = button({ hasDiff: false }, panel({ locked: true }));
			expect(wrapper.find(".k-button-badge").exists()).toBe(false);
		});
	});

	// methods
	describe("change()", () => {
		it("does nothing when selecting the current language", async () => {
			const $panel = panel();
			const wrapper = mount({}, $panel);
			await select(wrapper, "en");

			expect($panel.content.unlock).not.toHaveBeenCalled();
			expect($panel.reload).not.toHaveBeenCalled();
		});

		it("releases the lock and reloads when switching language", async () => {
			const $panel = panel();
			const wrapper = mount({}, $panel);
			await select(wrapper, "de");

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
			const wrapper = mount({}, $panel);
			await select(wrapper, "de");

			expect($panel.content.unlock).toHaveBeenCalledOnce();
			expect($panel.error).not.toHaveBeenCalled();
			expect($panel.reload).not.toHaveBeenCalled();
		});

		it("aborts the switch and reports when unlocking throws", async () => {
			// genuine failures still reach the regular error handler
			const error = new Error("Offline");
			const $panel = panel({ unlockError: error });
			const wrapper = mount({}, $panel);
			await select(wrapper, "de");

			expect($panel.error).toHaveBeenCalledWith(error);
			expect($panel.reload).not.toHaveBeenCalled();
		});
	});
});
