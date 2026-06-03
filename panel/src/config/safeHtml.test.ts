import { describe, expect, it } from "vitest";
import { mount } from "@vue/test-utils";
import { HtmlString } from "@/panel/html";
import safeHtml from "./safeHtml";

const TestComponent = {
	props: ["value"],
	template: `<div v-safe-html="value" />`
};

function mountWithDirective(value: unknown) {
	return mount(TestComponent, {
		props: { value },
		global: { plugins: [safeHtml] }
	});
}

describe("config.safeHtml", () => {
	describe("v-safe-html directive", () => {
		it("escapes a plain string", () => {
			const wrapper = mountWithDirective("<script>alert(1)</script>");
			expect(wrapper.find("script").exists()).toBe(false);
			expect(wrapper.element.innerHTML).toBe(
				"&lt;script&gt;alert(1)&lt;/script&gt;"
			);
		});

		it("renders an HtmlString as raw HTML", () => {
			const wrapper = mountWithDirective(
				new HtmlString("<strong>safe</strong>")
			);
			expect(wrapper.find("strong").exists()).toBe(true);
			expect(wrapper.find("strong").text()).toBe("safe");
		});

		it("renders null as empty string", () => {
			const wrapper = mountWithDirective(null);
			expect(wrapper.element.innerHTML).toBe("");
		});

		it("updates innerHTML when bound value changes", async () => {
			const wrapper = mountWithDirective("<b>1</b>");
			expect(wrapper.element.innerHTML).toBe("&lt;b&gt;1&lt;/b&gt;");

			await wrapper.setProps({ value: new HtmlString("<b>2</b>") });
			expect(wrapper.find("b").exists()).toBe(true);
			expect(wrapper.find("b").text()).toBe("2");
		});

		it("escapes when an HtmlString is replaced with a plain string", async () => {
			const wrapper = mountWithDirective(new HtmlString("<b>trusted</b>"));
			expect(wrapper.find("b").exists()).toBe(true);

			await wrapper.setProps({ value: "<b>untrusted</b>" });
			expect(wrapper.find("b").exists()).toBe(false);
			expect(wrapper.element.innerHTML).toBe("&lt;b&gt;untrusted&lt;/b&gt;");
		});
	});
});
