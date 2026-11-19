import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Definition from "./Definition.vue";

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(Definition, {
		props: { description: "5.3.0", term: "Version", ...props },
		attrs,
		slots
	});
}

describe("k-definition", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-definition");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// props
	describe("description prop", () => {
		it("describes the term", () => {
			expect(mount().find("dd").text()).toBe("5.3.0");
		});
	});

	describe("term prop", () => {
		it("names the term", () => {
			expect(mount().find("dt").text()).toBe("Version");
		});
	});

	// slots
	describe("default slot", () => {
		it("replaces the description", () => {
			const wrapper = mount({}, {}, { default: "<strong>6.0.0</strong>" });
			expect(wrapper.find("dd").html()).toContain("<strong>6.0.0</strong>");
			expect(wrapper.find("dd").text()).toBe("6.0.0");
		});
	});

	describe("term slot", () => {
		it("replaces the term", () => {
			const wrapper = mount({}, {}, { term: "<em>Release</em>" });
			expect(wrapper.find("dt").html()).toContain("<em>Release</em>");
		});
	});
});
