import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { h, nextTick, ref } from "vue";
import Highlight from "./Highlight.vue";

function mount(attrs = {}, text = ref("const a = 1;")) {
	return vueMount(Highlight, {
		attrs,
		attachTo: document.body,
		slots: {
			default: () => h("pre", { class: "language-js" }, [h("code", text.value)])
		}
	});
}

describe("k-highlight", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "PRE", "language-js");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// lifecycle
	describe("mounted hook", () => {
		it("highlights the code in its slot", () => {
			const wrapper = mount();
			expect(wrapper.find(".token.keyword").text()).toBe("const");
		});

		it("leaves code outside of it alone", () => {
			const outside = document.createElement("pre");
			outside.className = "language-js";
			outside.innerHTML = "<code>const b = 2;</code>";
			document.body.append(outside);

			mount();

			expect(outside.querySelector(".token")).toBeNull();
		});
	});

	describe("updated hook", () => {
		it("highlights the changed code in its slot", async () => {
			const text = ref("const a = 1;");
			const wrapper = mount({}, text);

			text.value = "let b = 2;";
			await nextTick();

			expect(wrapper.find(".token.keyword").text()).toBe("let");
		});
	});
});
