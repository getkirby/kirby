import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ButtonsField from "./ButtonsField.vue";

const buttons = [
	{ dialog: "users/a/changeEmail", icon: "email", text: "Email" },
	{ disabled: true, drawer: "users/a/totp", icon: "qr-code", text: "TOTP" }
];

function mount(props = {}, attrs = {}) {
	return vueMount(ButtonsField, {
		props: { buttons, ...props },
		attrs
	});
}

describe("k-buttons-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-buttons-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("renders a field without input", () => {
			const wrapper = mount();
			expect(wrapper.attributes("input")).toBe("false");
			expect(wrapper.attributes("type")).toBe("buttons");
		});
	});

	// props
	describe("buttons prop", () => {
		it("renders a filled k-button for each button", () => {
			const wrapper = mount();
			const items = wrapper.findAll("k-button-group k-button");
			expect(items.map((item) => item.attributes("text"))).toStrictEqual([
				"Email",
				"TOTP"
			]);
			expect(items[0].attributes("dialog")).toBe("users/a/changeEmail");
			expect(items[1].attributes("drawer")).toBe("users/a/totp");
			expect(items[0].attributes("variant")).toBe("filled");
		});

		it("shows the arrow only for enabled buttons", () => {
			const wrapper = mount();
			const items = wrapper.findAll("k-button");
			expect(items[0].attributes("dropdown")).toBe("true");
			expect(items[1].attributes("dropdown")).toBe("false");
		});

		it("renders k-empty without buttons", () => {
			const wrapper = mount({ buttons: [] });
			const empty = wrapper.find("k-empty");
			expect(empty.attributes("icon")).toBe("bars");
			expect(empty.text()).toBe("buttons.empty");
			expect(wrapper.find("k-button-group").exists()).toBe(false);
		});
	});

	describe("help prop", () => {
		it("passes the help text to the k-field", () => {
			const wrapper = mount({ help: "Some help" });
			expect(wrapper.attributes("help")).toBe("Some help");
		});
	});

	describe("id prop", () => {
		it("passes the id to the k-field", () => {
			const wrapper = mount({ id: "methods" });
			expect(wrapper.attributes("id")).toBe("methods");
		});
	});

	describe("label prop", () => {
		it("passes the label to the k-field", () => {
			const wrapper = mount({ label: "Methods" });
			expect(wrapper.attributes("label")).toBe("Methods");
		});
	});

	describe("name prop", () => {
		it("passes the name to the k-field", () => {
			const wrapper = mount({ name: "methods" });
			expect(wrapper.attributes("name")).toBe("methods");
		});
	});
});
