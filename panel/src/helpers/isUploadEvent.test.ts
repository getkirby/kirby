import { describe, expect, it } from "vitest";
import isUploadEvent from "./isUploadEvent";

const event = (dataTransfer: unknown): DragEvent =>
	({ dataTransfer }) as unknown as DragEvent;

describe("$helper.isUploadEvent()", () => {
	it("should return false without dataTransfer", () => {
		const e = event(null);
		expect(isUploadEvent(e)).toBe(false);
	});

	it("should return false without types", () => {
		const e = event({});
		expect(isUploadEvent(e)).toBe(false);
	});

	it("should return false when Files is not among the types", () => {
		const e = event({ types: ["text/plain"] });
		expect(isUploadEvent(e)).toBe(false);
	});

	it("should return false when text/plain is among the types", () => {
		const e = event({ types: ["Files", "text/plain"] });
		expect(isUploadEvent(e)).toBe(false);
	});

	it("should return true for a file-only drag", () => {
		const e = event({ types: ["Files"] });
		expect(isUploadEvent(e)).toBe(true);
	});
});
