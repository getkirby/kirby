/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import Modal, { defaults } from "./modal.js";
import Panel from "./panel.js";
import Vue from "vue";

describe.concurrent("panel/modal.js", () => {
	window.Vue = Vue;

	it("should have a default state", async () => {
		const panel = Panel.create();
		const modal = Modal(panel, "test", defaults());

		const state = {
			abortController: null,
			component: null,
			isLoading: false,
			on: {},
			path: null,
			props: {},
			query: {},
			referrer: null,
			timestamp: null
		};

		expect(modal.key()).toStrictEqual("test");
		expect(modal.state()).toStrictEqual(state);
	});

	it("should open and close", async () => {
		const panel = Panel.create();
		const modal = Modal(panel, "test", defaults());
		let opened = false;
		let closed = false;

		expect(modal.isOpen).toStrictEqual(false);

		await modal.open({
			component: "k-test",
			on: {
				close() {
					closed = true;
				},
				open() {
					opened = true;
				}
			}
		});

		expect(modal.isOpen).toStrictEqual(true);
		expect(opened).toStrictEqual(true);

		modal.close();

		expect(modal.isOpen).toStrictEqual(false);
		expect(closed).toStrictEqual(true);
	});

	it("should cancel", async () => {
		const panel = Panel.create();
		const modal = Modal(panel, "test", defaults());
		let cancelled = false;

		await modal.open({
			component: "k-test",
			on: {
				cancel() {
					cancelled = true;
				}
			}
		});

		expect(modal.isOpen).toStrictEqual(true);
		expect(modal.component).toStrictEqual("k-test");
		expect(cancelled).toStrictEqual(false);

		modal.cancel();

		expect(cancelled).toStrictEqual(true);
	});

	it("should close a previous notification", async () => {
		const panel = Panel.create();
		const modal = Modal(panel, "test", defaults());

		// open a notification first
		panel.notification.error("Foo");

		expect(modal.isOpen).toStrictEqual(false);
		expect(panel.notification.isOpen).toStrictEqual(true);

		await modal.open({
			component: "k-test"
		});

		expect(modal.isOpen).toStrictEqual(true);
		expect(panel.notification.isOpen).toStrictEqual(false);
	});

	it("should not close a previous notification on re-open", async () => {
		const panel = Panel.create();
		const modal = Modal(panel, "test", defaults());

		await modal.open({
			component: "k-test"
		});

		panel.notification.error("Test");

		expect(panel.notification.isOpen).toStrictEqual(true);

		await modal.open({
			component: "k-test"
		});

		expect(panel.notification.isOpen).toStrictEqual(true);
	});

	it("should change value", async () => {
		const panel = Panel.create();
		const modal = Modal(panel, "test", defaults());
		let input = null;

		await modal.open({
			component: "k-test",
			on: {
				input(value) {
					input = value;
				}
			}
		});

		modal.input("foo");

		expect(modal.props.value).toStrictEqual("foo");
		expect(modal.value).toStrictEqual("foo");
		expect(input).toStrictEqual("foo");
	});

	it("should send notification after submit", async () => {
		const panel = Panel.create();
		const modal = Modal(panel, "test", defaults());

		modal.success({
			message: "Test"
		});

		expect(panel.notification.message).toStrictEqual("Test");
		expect(panel.notification.theme).toStrictEqual("positive");
	});

	it("should emit panel events after submit", async () => {
		const panel = Panel.create();
		const modal = Modal(panel, "test", defaults());
		const emitted = [];

		panel.events.on("success", () => {
			emitted.push("success");
		});

		panel.events.on("user.deleted", () => {
			emitted.push("user.deleted");
		});

		modal.success({
			event: "user.deleted"
		});

		expect(emitted).toStrictEqual(["user.deleted", "success"]);
	});

	it("should close modal after submit", async () => {
		const panel = Panel.create();
		const modal = Modal(panel, "test", defaults());

		await modal.open({
			component: "k-test"
		});

		expect(modal.isOpen).toStrictEqual(true);

		modal.success({});

		expect(modal.isOpen).toStrictEqual(false);
	});
});
