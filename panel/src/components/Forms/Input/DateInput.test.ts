import { afterAll, beforeAll, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import dayjs from "@/libraries/dayjs";
import DateInput from "./DateInput.vue";

function mount(props: Record<string, unknown> = {}) {
	return vueMount(DateInput, {
		props: props as never,
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

describe("DateInput.vue", () => {
	beforeAll(() => {
		vi.useFakeTimers();
		vi.setSystemTime(new Date(2022, 0, 15));
	});

	afterAll(() => {
		vi.useRealTimers();
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "INPUT", "k-date-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	describe("placeholder", () => {
		it("shows an example value", () => {
			const input = mount({ display: "MMMM D, YYYY" }).find("input");
			expect(input.attributes("placeholder")).toBe("January 15, 2022");
		});

		it("follows the display pattern", () => {
			expect(
				mount({ display: "DD.MM.YYYY" }).find("input").attributes("placeholder")
			).toBe("15.01.2022");
		});

		it("uses `min` when now is before it", () => {
			const input = mount({
				display: "DD.MM.YYYY",
				min: "2024-06-23"
			}).find("input");

			expect(input.attributes("placeholder")).toBe("23.06.2024");
		});

		it("uses `max` when now is after it", () => {
			const input = mount({
				display: "DD.MM.YYYY",
				max: "2020-02-29"
			}).find("input");

			expect(input.attributes("placeholder")).toBe("29.02.2020");
		});

		it("stays on now when it is inside the boundaries", () => {
			const input = mount({
				display: "DD.MM.YYYY",
				min: "2020-01-01",
				max: "2024-12-31"
			}).find("input");

			expect(input.attributes("placeholder")).toBe("15.01.2022");
		});
	});

	describe("parse()", () => {
		it("reads input in the display pattern, not day-first", async () => {
			// https://github.com/getkirby/kirby/issues/7342
			const wrapper = mount({ display: "MM-DD-YYYY" });
			await type(wrapper, "06-26-2025");
			expect(emitted(wrapper)).toBe("2025-06-26");
		});

		it("accepts any separator and unpadded numbers", async () => {
			const wrapper = mount({ display: "MM-DD-YYYY" });
			await type(wrapper, "6/26/2025");
			expect(emitted(wrapper)).toBe("2025-06-26");
		});

		it("parses a pattern without a day part", async () => {
			// https://github.com/getkirby/kirby/issues/6408
			const wrapper = mount({ display: "MM/YYYY" });
			await type(wrapper, "08/2025");
			expect(emitted(wrapper)).toBe("2025-08-01");
		});

		it("resolves ambiguous input via the display pattern", async () => {
			const monthFirst = mount({ display: "MM/DD/YYYY" });
			await type(monthFirst, "05-03-2021");
			expect(emitted(monthFirst)).toBe("2021-05-03");

			const dayFirst = mount({ display: "DD.MM.YYYY" });
			await type(dayFirst, "05-03-2021");
			expect(emitted(dayFirst)).toBe("2021-03-05");
		});

		it("still falls back to interpret() for partial input", async () => {
			const wrapper = mount({ display: "DD.MM.YYYY" });
			await type(wrapper, "5");
			expect(emitted(wrapper)).toBe("2022-01-05");
		});
	});

	describe("onBlur()", () => {
		it("does not re-parse an untouched rendering of the value", async () => {
			// https://github.com/getkirby/kirby/issues/7342: a calendar-picked
			// value used to be corrupted by blurring the input it rendered into
			const wrapper = mount({
				display: "MM-DD-YYYY",
				value: "2025-06-26"
			});
			const input = wrapper.find("input");
			expect((input.element as HTMLInputElement).value).toBe("06-26-2025");

			await input.trigger("blur");

			expect(wrapper.emitted("input")).toBeUndefined();
			expect((input.element as HTMLInputElement).value).toBe("06-26-2025");
		});

		it("keeps unparseable text and flags the input as invalid", async () => {
			// https://github.com/getkirby/kirby/issues/6309
			const wrapper = mount({ display: "DD.MM.YYYY" });
			const input = await type(wrapper, "2. März 2024");

			// the text stays as it was typed, nothing is committed
			expect(input.value).toBe("2. März 2024");
			expect(wrapper.emitted("input")).toBeUndefined();
			expect(input.validationMessage).toBe("error.validation.date");
		});

		it("commits an empty string for empty input", async () => {
			const wrapper = mount({
				display: "DD.MM.YYYY",
				value: "2021-03-05"
			});
			await type(wrapper, "");
			expect(emitted(wrapper)).toBe("");
		});

		it("clears the invalid state once the input parses again", async () => {
			const wrapper = mount({ display: "DD.MM.YYYY" });
			const input = await type(wrapper, "not a date");
			expect(input.validationMessage).toBe("error.validation.date");

			await type(wrapper, "05.03.2021");
			expect(emitted(wrapper)).toBe("2021-03-05");
			expect(input.validationMessage).toBe("");
		});
	});
});
