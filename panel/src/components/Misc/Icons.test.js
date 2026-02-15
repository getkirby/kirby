import { describe, it, expect, vi } from "vitest";
import { mount } from "@vue/test-utils";
import Icons from "./Icons.vue";

vi.hoisted(() => {
	globalThis.panel = {
		plugins: {
			icons: {
				edit: '<path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/>',
				close:
					'<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>'
			}
		}
	};
});

describe("Icons.vue", () => {
	it("renders a <svg> with class k-icons", () => {
		const wrapper = mount(Icons);
		const svg = wrapper.find("svg");
		expect(svg.element.tagName).toBe("svg");
		expect(svg.classes()).toContain("k-icons");
	});

	it("is always aria-hidden", () => {
		const wrapper = mount(Icons);
		expect(wrapper.find("svg").attributes("aria-hidden")).toBe("true");
	});

	describe("icons", () => {
		it("renders a <symbol> for each icon", () => {
			const wrapper = mount(Icons);
			expect(wrapper.findAll("symbol")).toHaveLength(2);
		});

		it("sets the symbol id from the icon type", () => {
			const wrapper = mount(Icons);
			expect(wrapper.find("symbol#icon-edit").exists()).toBe(true);
			expect(wrapper.find("symbol#icon-close").exists()).toBe(true);
		});

		it("sets viewBox on each symbol", () => {
			const wrapper = mount(Icons);
			wrapper
				.findAll("symbol")
				.forEach((s) => expect(s.attributes("viewBox")).toBe("0 0 24 24"));
		});

		it("renders the icon svg content", () => {
			const wrapper = mount(Icons);
			const icon = wrapper.find("symbol#icon-edit");
			expect(icon.element.innerHTML).toContain(
				"M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"
			);
		});
	});
});
