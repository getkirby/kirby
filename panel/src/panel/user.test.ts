import { describe, expect, it } from "vitest";
import User from "./user";

describe("panel.user", () => {
	describe("reset()", () => {
		it("restores all default values", () => {
			const user = User();

			user.set({ id: "abc", username: "Kirby" });
			user.reset();

			expect(user.state()).toStrictEqual(user.defaults());
		});
	});

	describe("set()", () => {
		it("applies partial state", () => {
			const user = User();

			user.set({ id: "abc", username: "Kirby" });

			expect(user.id).toStrictEqual("abc");
			expect(user.username).toStrictEqual("Kirby");
			expect(user.email).toBeNull();
		});
	});

	describe("state()", () => {
		it("returns correct defaults", () => {
			const user = User();

			expect(user.state()).toStrictEqual({
				email: null,
				id: null,
				language: null,
				role: null,
				username: null
			});
		});
	});
});
