import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import html from "@/panel/html";
import Translation from "@/panel/translation";
import UserWebauthnDrawer from "./UserWebauthnDrawer.vue";

const translation = Translation();
translation.set({
	data: { "login.webauthn.remove.confirm": "Remove the passkey of {user}?" }
});

function panel() {
	return {
		dialog: { open: vi.fn() },
		html,
		user: { email: "admin@getkirby.com" }
	};
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(UserWebauthnDrawer, {
		props: {
			registration: {},
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

describe("k-user-webauthn-drawer", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-DRAWER", "k-user-webauthn-drawer");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// methods
	describe("onRemove()", () => {
		it("asks an admin for their password with the email of the user", () => {
			const $panel = panel();
			const wrapper = mount(
				{ user: { email: "o'brien@example.com" } },
				{},
				$panel
			);

			wrapper.vm.onRemove("a");

			const dialog = $panel.dialog.open.mock.calls[0][0];
			expect(dialog.component).toBe("k-form-dialog");
			expect(render(dialog.props.text).text()).toBe(
				"Remove the passkey of o'brien@example.com?"
			);
		});

		it("does not render HTML in the email of the user", () => {
			const $panel = panel();
			const wrapper = mount(
				{ user: { email: "<img src=x>@example.com" } },
				{},
				$panel
			);

			wrapper.vm.onRemove("a");

			const text = render($panel.dialog.open.mock.calls[0][0].props.text);
			expect(text.find("img").exists()).toBe(false);
			expect(text.text()).toBe(
				"Remove the passkey of <img src=x>@example.com?"
			);
		});
	});
});
