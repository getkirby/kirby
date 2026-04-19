import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Stats from "./Stats.vue";

const reports = [
	{ value: "50", label: "Pages" },
	{ value: "10", label: "Files" }
];

function mount(props = {}, attrs = {}) {
	return vueMount(Stats, {
		props: { reports, ...props },
		attrs,
		global: { mocks: { $t: (key: string) => key } }
	}).find(".k-stats");
}

describe("Stats.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DL", "k-stats");
	});

	// props
	describe("reports prop", () => {
		it("renders a k-stat for each report", () => {
			const wrapper = vueMount(Stats, {
				props: { reports },
				global: { mocks: { $t: (key: string) => key } }
			});
			expect(wrapper.findAll("k-stat")).toHaveLength(2);
		});

		it("renders k-empty when no reports passed", () => {
			const wrapper = vueMount(Stats, {
				props: { reports: [] },
				global: { mocks: { $t: (key: string) => key } }
			});
			expect(wrapper.find("k-empty").exists()).toBe(true);
			expect(wrapper.find(".k-stats").exists()).toBe(false);
		});
	});

	describe("size prop", () => {
		it("defaults to large", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-size")).toBe("large");
		});

		it("renders value as attribute", () => {
			const wrapper = mount({ size: "small" });
			expect(wrapper.attributes("data-size")).toBe("small");
		});
	});
});
