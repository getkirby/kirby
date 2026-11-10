import { describe, expect, it } from "@test/unit";
import { mount } from "@vue/test-utils";
import ModelTabs from "./ModelTabs.vue";

const Tabs = { name: "k-tabs", props: ["tab", "tabs"], template: "<nav />" };

const tabs = [
	{
		name: "main",
		fields: ["headline", "subheadline", "Text"]
	},
	{
		name: "meta",
		fields: ["seo"]
	}
];

type Badge = { text: number } | undefined;

/**
 * Returns the badges of the tabs that reach k-tabs
 */
function badges(diff = {}): Badge[] {
	const wrapper = mount(ModelTabs, {
		props: { tab: "main", tabs, diff },
		global: { stubs: { "k-tabs": Tabs } }
	});
	const received: { badge: Badge }[] = wrapper
		.findComponent(Tabs)
		.props("tabs");
	return received.map((tab) => tab.badge);
}

describe("k-model-tabs", () => {
	// $el
	describe("element", () => {
		it.rendersAs(ModelTabs, "K-TABS", "k-model-tabs");
		it.acceptsClass(ModelTabs);
		it.acceptsStyle(ModelTabs);
	});

	// computed
	describe("withBadges computed", () => {
		it("counts the changed fields of a tab", () => {
			expect(
				badges({ headline: "a", subheadline: "b", seo: "c" })
			).toStrictEqual([{ text: 2 }, { text: 1 }]);
		});

		it("compares the field names case-insensitively", () => {
			expect(badges({ text: "a" })).toStrictEqual([{ text: 1 }, undefined]);
		});

		it("has no badge without changes", () => {
			expect(badges()).toStrictEqual([undefined, undefined]);
		});
	});
});
