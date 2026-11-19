import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import InfoField from "./InfoField.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(InfoField, { props, attrs });
}

describe("k-info-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-info-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("is styled as a field", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-field");
		});
	});

	// props
	describe("help prop", () => {
		it("renders the help text in the footer", () => {
			const wrapper = mount({ help: "Some help" });
			const help = wrapper.find(".k-field-footer k-text");
			expect(help.attributes("text")).toBe("Some help");
			expect(help.classes()).toStrictEqual(["k-help", "k-field-help"]);
		});

		it("renders no footer without help", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-field-footer").exists()).toBe(false);
		});
	});

	describe("icon prop", () => {
		it("passes the icon to the k-box", () => {
			const wrapper = mount({ icon: "alert" });
			expect(wrapper.find("k-box").attributes("icon")).toBe("alert");
		});
	});

	describe("label prop", () => {
		it("renders the label as headline", () => {
			const wrapper = mount({ label: "Note" });
			expect(wrapper.find("k-headline").text()).toBe("Note");
		});

		it("renders no headline without label", () => {
			const wrapper = mount();
			expect(wrapper.find("k-headline").exists()).toBe(false);
		});
	});

	describe("text prop", () => {
		it("passes the text to the k-text in the k-box", () => {
			const wrapper = mount({ text: "Some info" });
			expect(wrapper.find("k-box k-text").attributes("text")).toBe("Some info");
		});
	});

	describe("theme prop", () => {
		it("defaults to info", () => {
			const wrapper = mount();
			expect(wrapper.find("k-box").attributes("theme")).toBe("info");
		});

		it("passes the theme to the k-box", () => {
			const wrapper = mount({ theme: "negative" });
			expect(wrapper.find("k-box").attributes("theme")).toBe("negative");
		});
	});
});
