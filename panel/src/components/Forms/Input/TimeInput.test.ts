import { afterAll, beforeAll, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import dayjs from "@/libraries/dayjs";
import TimeInput from "./TimeInput.vue";

function mount(props: Record<string, unknown> = {}) {
	return vueMount(TimeInput, {
		props: { step: { size: 1, unit: "minute" }, ...props } as never,
		global: {
			mocks: {
				$library: { dayjs },
				$t: (key: string) => key
			},
			directives: { direction: {} }
		}
	});
}

/**
 * Types into the input and blurs it, like a user would
 */
async function type(wrapper: ReturnType<typeof mount>, value: string) {
	const input = wrapper.find("input");
	await input.setValue(value);
	await input.trigger("blur");
	return input.element as HTMLInputElement;
}

/**
 * Last value the input emitted
 */
function emitted(wrapper: ReturnType<typeof mount>) {
	const events = wrapper.emitted("input");
	return events?.[events.length - 1]?.[0];
}

describe("TimeInput.vue", () => {
	beforeAll(() => {
		vi.useFakeTimers();
		vi.setSystemTime(new Date(2022, 0, 15));
	});

	afterAll(() => {
		vi.useRealTimers();
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "INPUT", "k-time-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	describe("placeholder", () => {
		it("shows an example time", () => {
			const input = mount({ display: "h:mm a" }).find("input");
			expect(input.attributes("placeholder")).toBe("12:00 am");
		});

		it("follows the display pattern", () => {
			expect(
				mount({ display: "HH:mm" }).find("input").attributes("placeholder")
			).toBe("00:00");
		});

		it("uses `min` when now is before it", () => {
			const input = mount({ display: "h:mm a", min: "13:00:00" }).find("input");
			expect(input.attributes("placeholder")).toBe("1:00 pm");
		});
	});

	describe("parse()", () => {
		it("reads input in the display pattern", async () => {
			const wrapper = mount({ display: "HH:mm" });
			await type(wrapper, "5:12");
			expect(emitted(wrapper)).toBe("05:12:00");
		});

		it("handles the meridiem of a 12-hour pattern", async () => {
			const wrapper = mount({ display: "h:mm a" });
			await type(wrapper, "5:12 pm");
			expect(emitted(wrapper)).toBe("17:12:00");

			await type(wrapper, "12:00 am");
			expect(emitted(wrapper)).toBe("00:00:00");
		});

		it("still falls back to interpret() for partial input", async () => {
			const wrapper = mount({ display: "HH:mm" });
			await type(wrapper, "9");
			expect(emitted(wrapper)).toBe("09:00:00");
		});

		it("reads a display pattern that escapes a literal", async () => {
			const wrapper = mount({ display: "HH[h]mm" });
			const input = await type(wrapper, "5h12");

			expect(emitted(wrapper)).toBe("05:12:00");
			expect(input.value).toBe("05h12");
		});

		it("reads input as a time even without a usable pattern", async () => {
			const wrapper = mount({ display: "foo" });
			await type(wrapper, "9");
			expect(emitted(wrapper)).toBe("09:00:00");
		});

		it("rounds to the nearest step", async () => {
			const wrapper = mount({
				display: "HH:mm",
				step: { size: 15, unit: "minute" }
			});
			await type(wrapper, "05:07");
			expect(emitted(wrapper)).toBe("05:00:00");
		});
	});

	describe("onBlur()", () => {
		it("does not re-parse an untouched rendering of the value", async () => {
			const wrapper = mount({ display: "h:mm a", value: "17:12:00" });
			const input = wrapper.find("input");
			expect((input.element as HTMLInputElement).value).toBe("5:12 pm");

			await input.trigger("blur");

			expect(wrapper.emitted("input")).toBeUndefined();
			expect((input.element as HTMLInputElement).value).toBe("5:12 pm");
		});

		it("keeps unparseable text and flags the input as invalid", async () => {
			const wrapper = mount({ display: "HH:mm" });
			const input = await type(wrapper, "25:61");

			expect(input.value).toBe("25:61");
			expect(wrapper.emitted("input")).toBeUndefined();
			expect(input.validationMessage).toBe("error.validation.time");
		});

		it("commits an empty string for empty input", async () => {
			const wrapper = mount({ display: "HH:mm", value: "17:12:00" });
			await type(wrapper, "");
			expect(emitted(wrapper)).toBe("");
		});
	});
});
