/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import Events from "./events.js";

describe.concurrent("panel.events.keychain", () => {
	const events = Events();

	it("should only add the type", async () => {
		const result = events.keychain("keydown", {});
		expect(result).toStrictEqual("keydown");
	});

	it("should add shift", async () => {
		const result = events.keychain("keydown", {
			shiftKey: true
		});
		expect(result).toStrictEqual("keydown.shift");
	});

	it("should add shift & key", async () => {
		const result = events.keychain("keydown", {
			shiftKey: true,
			key: "s"
		});

		expect(result).toStrictEqual("keydown.shift.s");
	});

	it("should add cmd", async () => {
		// Cmd
		const cmd = events.keychain("keydown", {
			metaKey: true
		});

		expect(cmd).toStrictEqual("keydown.cmd");

		// Ctrl
		const ctrl = events.keychain("keydown", {
			ctrlKey: true
		});

		expect(ctrl).toStrictEqual("keydown.cmd");
	});

	it("should add cmd & key", async () => {
		const result = events.keychain("keydown", {
			metaKey: true,
			key: "s"
		});

		expect(result).toStrictEqual("keydown.cmd.s");
	});

	it("should add alt", async () => {
		const result = events.keychain("keydown", {
			altKey: true
		});

		expect(result).toStrictEqual("keydown.alt");
	});

	it("should add alt & key", async () => {
		const result = events.keychain("keydown", {
			altKey: true,
			key: "v"
		});

		expect(result).toStrictEqual("keydown.alt.v");
	});

	it("should add key replacement", async () => {
		const esc = events.keychain("keydown", {
			key: "escape"
		});

		expect(esc).toStrictEqual("keydown.esc");

		const up = events.keychain("keydown", {
			key: "arrowUp"
		});

		expect(up).toStrictEqual("keydown.up");

		const down = events.keychain("keydown", {
			key: "arrowDown"
		});

		expect(down).toStrictEqual("keydown.down");

		const left = events.keychain("keydown", {
			key: "arrowLeft"
		});

		expect(left).toStrictEqual("keydown.left");

		const right = events.keychain("keydown", {
			key: "arrowRight"
		});

		expect(right).toStrictEqual("keydown.right");
	});

	it("should add combo", async () => {
		const result = events.keychain("keydown", {
			metaKey: true,
			shiftKey: true,
			altKey: true,
			key: "escape"
		});

		expect(result).toStrictEqual("keydown.cmd.alt.shift.esc");
	});
});
