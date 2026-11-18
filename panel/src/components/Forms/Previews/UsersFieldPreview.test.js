import Vue from "vue";
import { describe, expect, it } from "vitest";
import Tag from "@/components/Navigation/Tag.vue";
import UsersFieldPreview from "./UsersFieldPreview.vue";

const mount = (value) => {
	const Preview = Vue.extend({
		components: { "k-tag": Tag },
		extends: UsersFieldPreview
	});

	return new Preview({ propsData: { value } }).$mount().$el;
};

describe("UsersFieldPreview", () => {
	it("should escape markup in the username", () => {
		const username = '<img src="x">';
		const el = mount([{ username }]);

		expect(el.querySelector("img")).toBeNull();
		expect(el.querySelector(".k-tag-text").textContent).toBe(username);
	});

	it("should fall back to the email", () => {
		const el = mount([{ email: "homer@simpsons.com" }]);

		expect(el.querySelector(".k-tag-text").textContent).toBe(
			"homer@simpsons.com"
		);
	});
});
