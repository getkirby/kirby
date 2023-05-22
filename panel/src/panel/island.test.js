import { describe, expect, it, vi } from "vitest";
import Island, { defaults } from "./island.js";
import Panel from "./panel.js";
import Vue from "vue";

describe.concurrent("panel/island.js", () => {
	window.Vue = Vue;

	it("should have a default state", async () => {
		const panel = Panel.create();
		const island = Island(panel, "test", defaults());

		const state = {
			component: null,
			isLoading: false,
			isOpen: false,
			island: true,
			on: {},
			path: null,
			props: {},
			query: {},
			ref: null,
			referrer: null,
			timestamp: null
		};

		expect(island.key()).toStrictEqual("test");
		expect(island.state()).toStrictEqual(state);
	});

	it("should open and close", async () => {
		const panel = Panel.create();
		const island = Island(panel, "test", defaults());
		let opened = false;
		let closed = false;

		expect(island.isOpen).toStrictEqual(false);

		await island.open({
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

		expect(island.isOpen).toStrictEqual(true);
		expect(opened).toStrictEqual(true);

		island.close();

		expect(island.isOpen).toStrictEqual(false);
		expect(closed).toStrictEqual(true);
	});

	it("should cancel", async () => {
		const panel = Panel.create();
		const island = Island(panel, "test", defaults());
		let cancelled = false;

		await island.open({
			component: "k-test",
			on: {
				cancel() {
					cancelled = true;
				}
			}
		});

		expect(island.isOpen).toStrictEqual(true);
		expect(island.component).toStrictEqual("k-test");
		expect(cancelled).toStrictEqual(false);

		island.cancel();

		expect(cancelled).toStrictEqual(true);
	});

	it("should close a previous notification", async () => {
		const panel = Panel.create();
		const island = Island(panel, "test", defaults());

		// open a notification first
		panel.notification.error("Foo");

		expect(island.isOpen).toStrictEqual(false);
		expect(panel.notification.isOpen).toStrictEqual(true);

		await island.open({
			component: "k-test"
		});

		expect(island.isOpen).toStrictEqual(true);
		expect(panel.notification.isOpen).toStrictEqual(false);
	});

	it("should not close a previous notification on re-open", async () => {
		const panel = Panel.create();
		const island = Island(panel, "test", defaults());

		await island.open({
			component: "k-test"
		});

		panel.notification.error("Test");

		expect(panel.notification.isOpen).toStrictEqual(true);

		await island.open({
			component: "k-test"
		});

		expect(panel.notification.isOpen).toStrictEqual(true);
	});

	it("should change value", async () => {
		const panel = Panel.create();
		const island = Island(panel, "test", defaults());
		let input = null;

		await island.open({
			component: "k-test",
			on: {
				input(value) {
					input = value;
				}
			}
		});

		island.input("foo");

		expect(island.props.value).toStrictEqual("foo");
		expect(island.value).toStrictEqual("foo");
		expect(input).toStrictEqual("foo");
	});

	it("should send notification after submit", async () => {
		const panel = Panel.create();
		const island = Island(panel, "test", defaults());

		island.success({
			message: "Test"
		});

		expect(panel.notification.type).toStrictEqual("success");
		expect(panel.notification.message).toStrictEqual("Test");
	});

	it("should emit panel events after submit", async () => {
		const panel = Panel.create();
		const island = Island(panel, "test", defaults());
		const emitted = [];

		panel.events.on("success", () => {
			emitted.push("success");
		});

		panel.events.on("user.deleted", () => {
			emitted.push("user.deleted");
		});

		island.success({
			event: "user.deleted"
		});

		expect(emitted).toStrictEqual(["user.deleted", "success"]);
	});

	it("should close island after submit", async () => {
		const panel = Panel.create();
		const island = Island(panel, "test", defaults());

		await island.open({
			component: "k-test"
		});

		expect(island.isOpen).toStrictEqual(true);

		island.success({});

		expect(island.isOpen).toStrictEqual(false);
	});
});
