/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import Notification from "./notification.js";

describe.concurrent("panel.notification", () => {
	it("should have a default state", async () => {
		const notification = Notification();

		const state = {
			details: null,
			isOpen: false,
			message: null,
			timeout: null,
			type: null
		};

		expect(notification.key()).toStrictEqual("notification");
		expect(notification.state()).toStrictEqual(state);
	});

	it("should open & close", async () => {
		const notification = Notification();

		notification.open({
			message: "Hello world"
		});

		expect(notification.message).toStrictEqual("Hello world");
		expect(notification.isOpen).toStrictEqual(true);

		notification.close();

		expect(notification.isOpen).toStrictEqual(false);
	});

	it("should return the correct context", async () => {
		const panel = {
			dialog: {},
			drawer: {}
		};

		const notification = Notification(panel);

		expect(notification.context).toStrictEqual(false);

		notification.isOpen = true;

		expect(notification.context).toStrictEqual("view");

		panel.drawer.isOpen = true;

		expect(notification.context).toStrictEqual("drawer");

		panel.dialog.isOpen = true;

		expect(notification.context).toStrictEqual("dialog");
	});

	it("should return the right icon", async () => {
		const notification = Notification();

		notification.success("Test");

		expect(notification.icon).toStrictEqual("check");

		notification.error("Test");

		expect(notification.icon).toStrictEqual("alert");
	});

	it("should return the right theme", async () => {
		const notification = Notification();

		notification.success("Test");

		expect(notification.theme).toStrictEqual("positive");

		notification.error("Test");

		expect(notification.theme).toStrictEqual("negative");
	});

	it("should set a timer for success notifications", async () => {
		const notification = Notification();

		notification.success("Test");
		expect(notification.timeout).toStrictEqual(4000);
	});

	it("should not set a timer for error notifications", async () => {
		const notification = Notification();

		notification.error("Test");
		expect(notification.timeout).toStrictEqual(null);
	});

	it("should reset the timer when closing notifications", async () => {
		const notification = Notification();

		notification.success("Test");
		expect(notification.timer.interval).toBeTypeOf("object");

		notification.close();
		expect(notification.timer.interval).toStrictEqual(null);
	});

	it("should convert Error objects", async () => {
		const notification = Notification();

		notification.error(new Error("test"));

		expect(notification.type).toStrictEqual("error");
		expect(notification.message).toStrictEqual("test");
	});
});
