import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { read, write } from "./clipboard";

class MockDataTransfer {
	private data: Record<string, string> = {};
	types: string[] = [];

	setData(type: string, value: string) {
		this.data[type] = value;
		if (!this.types.includes(type)) {
			this.types.push(type);
		}
	}

	getData(type: string) {
		return this.data[type] ?? "";
	}
}

class MockClipboardEvent extends Event {
	clipboardData: MockDataTransfer | null;

	constructor(type: string, init?: { clipboardData?: MockDataTransfer }) {
		super(type);
		this.clipboardData = init?.clipboardData ?? null;
	}
}

vi.stubGlobal("DataTransfer", MockDataTransfer);
vi.stubGlobal("ClipboardEvent", MockClipboardEvent);

describe("clipboard.read()", () => {
	it("should return null for undefined", () => {
		expect(read(undefined)).toBeNull();
	});

	it("should return the string if a string is passed", () => {
		expect(read("hello")).toBe("hello");
	});

	it("should return null for a non-ClipboardEvent", () => {
		expect(read(new Event("paste"))).toBeNull();
	});

	it("should return null when clipboardData is null", () => {
		expect(read(new MockClipboardEvent("paste"))).toBeNull();
	});

	it("should return text when plain is true", () => {
		const dataTransfer = new MockDataTransfer();
		dataTransfer.setData("text/plain", "hello");
		const event = new MockClipboardEvent("paste", {
			clipboardData: dataTransfer
		});
		expect(read(event, true)).toBe("hello");
	});

	it("should prefer text/html over text/plain", () => {
		const dataTransfer = new MockDataTransfer();
		dataTransfer.setData("text/html", "<b>hello</b>");
		dataTransfer.setData("text/plain", "hello");
		const event = new MockClipboardEvent("paste", {
			clipboardData: dataTransfer
		});
		expect(read(event)).toBe("<b>hello</b>");
	});

	it("should fall back to text/plain when text/html is absent", () => {
		const dataTransfer = new MockDataTransfer();
		dataTransfer.setData("text/plain", "hello");
		const event = new MockClipboardEvent("paste", {
			clipboardData: dataTransfer
		});
		expect(read(event)).toBe("hello");
	});

	it("should replace non-breaking spaces", () => {
		const dataTransfer = new MockDataTransfer();
		dataTransfer.setData("text/plain", "hello\u00a0world");
		const event = new MockClipboardEvent("paste", {
			clipboardData: dataTransfer
		});
		expect(read(event)).toBe("hello world");
	});
});

describe("clipboard.write()", () => {
	beforeEach(() => {
		document.execCommand = vi.fn().mockReturnValue(true);
	});

	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("should use clipboardData.setData when a ClipboardEvent is provided", () => {
		const dataTransfer = new MockDataTransfer();
		const setData = vi.spyOn(dataTransfer, "setData");
		const event = new MockClipboardEvent("copy", {
			clipboardData: dataTransfer
		});

		const result = write("hello", event);

		expect(result).toBe(true);
		expect(setData).toHaveBeenCalledWith("text/plain", "hello");
	});

	it("should serialize objects to JSON before writing", () => {
		const dataTransfer = new MockDataTransfer();
		const setData = vi.spyOn(dataTransfer, "setData");
		const event = new MockClipboardEvent("copy", {
			clipboardData: dataTransfer
		});

		write({ foo: "bar" }, event);

		expect(setData).toHaveBeenCalledWith("text/plain", '{\n  "foo": "bar"\n}');
	});

	it("should copy the value via execCommand fallback", () => {
		let copiedValue: string | null = null;
		document.execCommand = vi.fn().mockImplementation(() => {
			copiedValue = document.querySelector("textarea")?.value ?? null;
			return true;
		});

		write("hello");

		expect(copiedValue).toBe("hello");
	});

	it("should serialize objects to JSON when using execCommand fallback", () => {
		let copiedValue: string | null = null;
		document.execCommand = vi.fn().mockImplementation(() => {
			copiedValue = document.querySelector("textarea")?.value ?? null;
			return true;
		});

		write({ foo: "bar" });

		expect(copiedValue).toBe('{\n  "foo": "bar"\n}');
	});
});
