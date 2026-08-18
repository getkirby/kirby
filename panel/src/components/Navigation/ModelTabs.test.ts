import { describe, expect, it } from "@test/unit";
import { mount } from "@vue/test-utils";
import ModelTabs from "./ModelTabs.vue";

const tabs = [
	{
		name: "main",
		fields: ["headline", "Text"]
	},
	{
		name: "meta",
		fields: ["seo"]
	}
];

type Badge = { text: number } | undefined;

function badges(diff = {}): Badge[] {
	const wrapper = mount(ModelTabs, { props: { tab: "main", tabs, diff } });
	const withBadges = wrapper.vm.withBadges as { badge: Badge }[];
	return withBadges.map((tab) => tab.badge);
}

describe("ModelTabs.vue", () => {
	describe("element", () => {
		it.rendersAs(() => mount(ModelTabs).find("k-tabs"), "K-TABS");
	});

	describe("withBadges", () => {
		it("counts the changed fields of a tab", () => {
			expect(badges({ headline: "a", seo: "b" })).toStrictEqual([
				{ text: 1 },
				{ text: 1 }
			]);
		});

		it("compares the field names case-insensitively", () => {
			expect(badges({ text: "a" })).toStrictEqual([{ text: 1 }, undefined]);
		});

		it("has no badge without changes", () => {
			expect(badges()).toStrictEqual([undefined, undefined]);
		});
	});
});
