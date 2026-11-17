import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import StatsField from "./StatsField.vue";

const Stats = {
	name: "k-stats",
	props: ["reports", "size"],
	template: "<dl />"
};
const reports = [{ label: "Pages", value: "50" }];

function mount(props = {}, attrs = {}) {
	return vueMount(StatsField, {
		props: { reports, ...props },
		attrs,
		global: {
			stubs: { "k-stats": Stats }
		}
	});
}

describe("k-stats-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-stats-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("renders a field without input", () => {
			const wrapper = mount();
			expect(wrapper.attributes("input")).toBe("false");
			expect(wrapper.attributes("type")).toBe("stats");
		});
	});

	// props
	describe("help prop", () => {
		it("passes the help text to the k-field", () => {
			const wrapper = mount({ help: "Some help" });
			expect(wrapper.attributes("help")).toBe("Some help");
		});
	});

	describe("id prop", () => {
		it("passes the id to the k-field", () => {
			const wrapper = mount({ id: "stats" });
			expect(wrapper.attributes("id")).toBe("stats");
		});
	});

	describe("label prop", () => {
		it("passes the label to the k-field", () => {
			const wrapper = mount({ label: "Stats" });
			expect(wrapper.attributes("label")).toBe("Stats");
		});
	});

	describe("name prop", () => {
		it("passes the name to the k-field", () => {
			const wrapper = mount({ name: "stats" });
			expect(wrapper.attributes("name")).toBe("stats");
		});
	});

	describe("reports prop", () => {
		it("passes the reports to the k-stats", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Stats).props("reports")).toStrictEqual(
				reports
			);
		});
	});

	describe("size prop", () => {
		it("passes the size to the k-stats", () => {
			const wrapper = mount({ size: "small" });
			expect(wrapper.findComponent(Stats).props("size")).toBe("small");
		});
	});
});
