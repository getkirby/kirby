import { describe, expect, it, beforeEach } from "vitest";
import { mount } from "@vue/test-utils";
import Panel from "@/panel/panel";
import i18n from "./i18n";

const TestComponent = {
	props: { disabled: { type: Boolean, default: false } },
	template: `<div v-direction></div>`
};

function mountWithDirective(disabled = false) {
	return mount(TestComponent, {
		props: { disabled },
		global: { plugins: [i18n] }
	});
}

describe("config.i18n", () => {
	describe("v-direction directive", () => {
		beforeEach(() => {
			window.panel = Panel.create(app);
		});

		it("sets el.dir from Panel language when component is not disabled", () => {
			const wrapper = mountWithDirective(false);
			expect(wrapper.element.dir).toBe("ltr");
		});

		it("removes dir attribute when component is disabled", () => {
			const wrapper = mountWithDirective(true);
			expect(wrapper.element.hasAttribute("dir")).toBe(false);
		});

		it("updates el.dir when disabled changes to false", async () => {
			const wrapper = mountWithDirective(true);
			await wrapper.setProps({ disabled: false });
			expect(wrapper.element.dir).toBe("ltr");
		});

		it("removes dir attribute when disabled changes to true", async () => {
			const wrapper = mountWithDirective(false);
			await wrapper.setProps({ disabled: true });
			expect(wrapper.element.hasAttribute("dir")).toBe(false);
		});

		it("reflects Panel language direction", () => {
			window.panel.language.set({ direction: "rtl" });
			const wrapper = mountWithDirective(false);
			expect(wrapper.element.dir).toBe("rtl");

			window.panel.language.set({ direction: "ltr" });
			expect(wrapper.element.dir).toBe("rtl");
		});
	});
});
