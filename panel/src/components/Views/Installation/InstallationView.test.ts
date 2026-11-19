import { describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import InstallationView from "./InstallationView.vue";

const Button = {
	name: "k-button",
	props: ["icon", "link", "size", "text", "theme", "type", "variant"],
	emits: ["click"],
	template: `<button :type="type" @click="$emit('click', $event)" />`
};
const Fieldset = {
	name: "k-fieldset",
	props: ["fields", "value"],
	emits: ["input"],
	template: "<fieldset />"
};
const Outside = {
	name: "k-panel-outside",
	template: "<div><slot /></div>"
};

const translations = [
	{ text: "English", value: "en" },
	{ text: "Deutsch", value: "de" }
];

function panel() {
	return {
		error: vi.fn(),
		notification: { success: vi.fn() },
		reload: vi.fn(),
		translation: { code: "en" }
	};
}

function mount(props = {}, attrs = {}, $panel = panel(), $api = api()) {
	return vueMount(InstallationView, {
		props: { isInstallable: true, isOk: true, translations, ...props },
		attrs,
		global: {
			mocks: { $api, $panel },
			stubs: {
				"k-button": Button,
				"k-fieldset": Fieldset,
				"k-panel-outside": Outside
			}
		}
	});
}

function api() {
	return { system: { install: vi.fn(async () => ({})) } };
}

describe("k-installation-view", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-installation-view");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("names the view for screen readers", () => {
			expect(mount().find("h1.sr-only").text()).toBe("installation");
		});
	});

	// props
	describe("isInstallable prop", () => {
		it("asks for the first user", () => {
			const wrapper = mount();
			const fieldset = wrapper.findComponent(Fieldset);
			expect(Object.keys(fieldset.props("fields"))).toStrictEqual([
				"email",
				"password",
				"language"
			]);
			expect(fieldset.props("fields").language.options).toStrictEqual(
				translations
			);
			expect(fieldset.props("value")).toStrictEqual({
				email: "",
				language: "en",
				name: "",
				password: "",
				role: "admin"
			});
			expect(wrapper.findComponent(Button).props()).toMatchObject({
				icon: "check",
				text: "install",
				theme: "positive",
				type: "submit"
			});
		});

		it("explains when the installation is disabled", () => {
			const wrapper = mount({ isInstallable: false, requirements: {} });
			expect(wrapper.findAll(".k-installation-issues li")).toHaveLength(1);
			expect(wrapper.find(".k-installation-issues").text()).toBe(
				"installation.disabled"
			);
		});
	});

	describe("isInstalled prop", () => {
		it("links to the login once installed", () => {
			const wrapper = mount({ isInstallable: false, isInstalled: true });
			expect(wrapper.find("p").text()).toBe("installation.completed.");
			expect(wrapper.findComponent(Button).props()).toMatchObject({
				icon: "check",
				link: "/login",
				text: "login"
			});
			expect(wrapper.findComponent(Fieldset).exists()).toBe(false);
		});
	});

	describe("isOk prop", () => {
		it("lists the issues of an installed but broken Panel", () => {
			const wrapper = mount({
				isInstallable: false,
				isInstalled: true,
				isOk: false,
				requirements: { content: false }
			});
			expect(wrapper.find("p").exists()).toBe(false);
			expect(
				wrapper.findAll(".k-installation-issues li").map((li) => li.text())
			).toStrictEqual(["installation.disabled", "installation.issues.content"]);
		});
	});

	describe("requirements prop", () => {
		it("lists the missing requirements", () => {
			const wrapper = mount({
				isOk: false,
				requirements: {
					accounts: false,
					content: true,
					extensions: { gd: true, mbstring: false },
					media: false,
					sessions: true
				}
			});
			expect(
				wrapper.findAll(".k-installation-issues li").map((li) => li.text())
			).toStrictEqual([
				"installation.issues.extension",
				"installation.issues.accounts",
				"installation.issues.media"
			]);
			expect(wrapper.findComponent(Fieldset).exists()).toBe(false);
		});

		it("retries after fixing the issues", async () => {
			const $panel = panel();
			const wrapper = mount({ isOk: false, requirements: {} }, {}, $panel);
			const retry = wrapper.findComponent(Button);
			expect(retry.props()).toMatchObject({ icon: "refresh", text: "retry" });
			await retry.trigger("click");
			expect($panel.reload).toHaveBeenCalledOnce();
		});
	});

	// methods
	describe("install()", () => {
		it("installs the Panel with the first user", async () => {
			const $api = api();
			const $panel = panel();
			const wrapper = mount({}, {}, $panel, $api);
			const user = {
				email: "a@getkirby.com",
				language: "de",
				password: "12345678"
			};
			wrapper.findComponent(Fieldset).vm.$emit("input", user);
			await wrapper.find("form").trigger("submit");
			await flushPromises();
			expect($api.system.install).toHaveBeenCalledWith(user);
			expect($panel.reload).toHaveBeenCalledWith({ globals: ["system"] });
			expect($panel.notification.success).toHaveBeenCalledWith({
				icon: "smile",
				message: "welcome!"
			});
		});

		it("shows an error", async () => {
			const $api = api();
			const $panel = panel();
			const error = new Error("Invalid email");
			$api.system.install.mockRejectedValueOnce(error);
			const wrapper = mount({}, {}, $panel, $api);
			await wrapper.vm.install();
			expect($panel.error).toHaveBeenCalledWith(error);
			expect($panel.reload).not.toHaveBeenCalled();
		});
	});
});
