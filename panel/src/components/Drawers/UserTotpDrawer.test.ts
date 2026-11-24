import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import html from "@/panel/html";
import Translation from "@/panel/translation";
import UserTotpDrawer from "./UserTotpDrawer.vue";

const translation = Translation();
translation.set({
	data: { "login.totp.disable.confirm": "Disable codes for {user}?" }
});

function panel() {
	return {
		dialog: { open: vi.fn() },
		html,
		user: { email: "admin@getkirby.com" }
	};
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(UserTotpDrawer, {
		props: {
			qr: "<svg></svg>",
			user: { email: "test@getkirby.com" },
			...props
		},
		attrs,
		global: {
			mocks: {
				$panel,
				$t: translation.translate.bind(translation),
				$th: translation.translateHtml.bind(translation)
			}
		}
	});
}

// renders the text like k-text does
function render(text: unknown) {
	return vueMount(
		{ props: ["text"], template: `<p v-safe-html="text" />` },
		{ props: { text } }
	);
}

describe("k-user-totp-drawer", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-DRAWER", "k-user-totp-drawer");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// methods
	describe("disable()", () => {
		it("asks an admin for their password with the email of the user", () => {
			const $panel = panel();
			const wrapper = mount(
				{ isEnabled: true, user: { email: "o'brien@example.com" } },
				{},
				$panel
			);

			wrapper.vm.disable();

			const dialog = $panel.dialog.open.mock.calls[0][0];
			expect(dialog.component).toBe("k-form-dialog");
			expect(render(dialog.props.text).text()).toBe(
				"Disable codes for o'brien@example.com?"
			);
		});

		it("does not render HTML in the email of the user", () => {
			const $panel = panel();
			const wrapper = mount(
				{ isEnabled: true, user: { email: "<img src=x>@example.com" } },
				{},
				$panel
			);

			wrapper.vm.disable();

			const text = render($panel.dialog.open.mock.calls[0][0].props.text);
			expect(text.find("img").exists()).toBe(false);
			expect(text.text()).toBe("Disable codes for <img src=x>@example.com?");
		});
	});
});
