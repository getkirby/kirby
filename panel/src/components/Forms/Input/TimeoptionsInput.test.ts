import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TimeoptionsInput from "./TimeoptionsInput.vue";

function mount(props: Record<string, unknown> = {}) {
	return vueMount(TimeoptionsInput, {
		props: props as never
	});
}

function labels(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll("k-button").map((button) => button.text());
}

describe("k-timeoptions-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-timeoptions-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// computed
	describe("day computed", () => {
		it("starts in the morning", () => {
			expect(labels(mount())[0]).toBe("06:00");
		});
	});

	describe("night computed", () => {
		it("runs into the small hours", () => {
			expect(labels(mount()).at(-1)).toBe("05:00");
		});
	});

	// methods
	describe("formatTimes()", () => {
		it("renders every option as a time", () => {
			const times = labels(mount());
			expect(times).toHaveLength(24);

			for (const label of times) {
				expect(label).toMatch(/^\d{2}:\d{2}$/);
			}
		});

		it("follows the display pattern", () => {
			const times = labels(mount({ display: "h:mm a" }));
			expect(times[0]).toBe("6:00 am");
			expect(times.at(-1)).toBe("5:00 am");
		});

		it("marks only the option the value selects", () => {
			const wrapper = mount({ value: "14:00:00" });
			const buttons = wrapper.findAll("k-button");
			const selected = buttons.filter(
				(button) => button.attributes("selected") !== "false"
			);

			expect(buttons).toHaveLength(24);
			expect(selected).toHaveLength(1);
			expect(selected[0].text()).toBe("14:00");
			expect(selected[0].attributes("selected")).toBe("time");
		});
	});

	describe("select()", () => {
		it("emits the time as an ISO string", async () => {
			const wrapper = mount();
			await wrapper.findAll("k-button")[0].trigger("click");
			expect(wrapper.emitted("input")![0][0]).toBe("06:00:00");
		});
	});
});
