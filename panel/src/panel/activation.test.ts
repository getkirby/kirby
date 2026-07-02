import { describe, expect, it, beforeEach } from "vitest";
import Activation from "./activation";

describe("panel.activation", () => {
	beforeEach(() => {
		sessionStorage.clear();
	});

	describe("close()", () => {
		it("hides the card and persists to sessionStorage", () => {
			const activation = Activation();
			expect(activation.isOpen).toStrictEqual(true);

			activation.close();

			expect(activation.isOpen).toStrictEqual(false);
			expect(sessionStorage.getItem("kirby$activation$card")).toStrictEqual(
				"true"
			);
		});
	});

	describe("isOpen", () => {
		it("is open by default", () => {
			const activation = Activation();
			expect(activation.isOpen).toStrictEqual(true);
		});

		it("is closed by default when marked in sessionStorage", () => {
			sessionStorage.setItem("kirby$activation$card", "true");
			const activation = Activation();
			expect(activation.isOpen).toStrictEqual(false);
		});
	});

	describe("open()", () => {
		it("shows the card and clears sessionStorage", () => {
			sessionStorage.setItem("kirby$activation$card", "true");
			const activation = Activation();
			expect(activation.isOpen).toStrictEqual(false);

			activation.open();

			expect(activation.isOpen).toStrictEqual(true);
			expect(sessionStorage.getItem("kirby$activation$card")).toStrictEqual(
				null
			);
		});
	});
});
