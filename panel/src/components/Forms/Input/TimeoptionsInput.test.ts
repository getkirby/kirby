import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import dayjs from "@/libraries/dayjs";
import TimeoptionsInput from "./TimeoptionsInput.vue";

function mount(props: Record<string, unknown> = {}) {
	return vueMount(TimeoptionsInput, {
		props: props as never,
		global: {
			mocks: {
				$library: { dayjs },
				$t: (key: string) => key
			}
		}
	});
}

function labels(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll("k-button").map((button) => button.text());
}

describe("TimeoptionsInput.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-timeoptions-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	describe("day", () => {
		it("starts in the morning", () => {
			expect(labels(mount())[0]).toBe("06:00");
		});
	});

	describe("night", () => {
		it("runs into the small hours", () => {
			expect(labels(mount()).at(-1)).toBe("05:00");
		});
	});

	describe("formatTimes()", () => {
		it("renders every option as a time", () => {
			for (const label of labels(mount())) {
				expect(label).toMatch(/^\d{2}:\d{2}$/);
			}
		});

		it("follows the display pattern", () => {
			const times = labels(mount({ display: "h:mm a" }));
			expect(times[0]).toBe("6:00 am");
			expect(times.at(-1)).toBe("5:00 am");
		});

		it("marks the option the value selects", () => {
			const wrapper = mount({ value: "06:00:00" });
			const button = wrapper.findAll("k-button")[0];
			expect(button.attributes("selected")).toBe("time");
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
