/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import Feature, { defaults } from "./feature.js";

// dummy panel to avoid dependencies
const Panel = () => {
	return {
		dialog: {},
		drawer: {},
		open(url, options) {
			return { url, options };
		},
		url(path) {
			return path;
		}
	};
};

describe.concurrent("panel/feature.js", () => {
	it("should add event listeners", async () => {
		const feature = Feature(Panel(), "test", defaults());
		const listeners = {
			submit: () => {},
			cancel: () => {}
		};

		feature.addEventListeners(listeners);

		expect(feature.on).toStrictEqual(listeners);
	});

	it("should ignore invalid event listeners", async () => {
		const feature = Feature(Panel(), "test", defaults());
		const listeners = {
			submit: () => {},
			cancel: () => {}
		};

		feature.addEventListeners({
			...listeners,
			foo: "bar"
		});

		expect(feature.on).toStrictEqual(listeners);
	});

	it("should detect event listeners", async () => {
		const feature = Feature(Panel(), "test", defaults());
		const listeners = {
			submit: () => {}
		};

		feature.addEventListeners(listeners);

		expect(feature.hasEventListener("submit")).toStrictEqual(true);
		expect(feature.hasEventListener("cancel")).toStrictEqual(false);
	});

	it("should emit events", async () => {
		const feature = Feature(Panel(), "test", defaults());
		let emitted = false;

		const listeners = {
			submit: () => {
				emitted = true;
			}
		};

		feature.addEventListeners({
			...listeners
		});

		feature.emit("submit");

		expect(emitted).toStrictEqual(true);
	});

	it("should set state", async () => {
		const feature = Feature(Panel(), "test", defaults());

		// would only work with a full panel object
		feature.set({
			component: "k-test"
		});

		expect(feature.component).toStrictEqual("k-test");
		expect(feature.on).toStrictEqual({});
	});

	it("should set state with event listeners", async () => {
		const feature = Feature(Panel(), "test", defaults());

		const listeners = {
			submit: () => {}
		};

		// would only work with a full panel object
		feature.set({
			component: "k-test",
			on: {
				...listeners,
				ignoreMe: null
			}
		});

		expect(feature.component).toStrictEqual("k-test");
		expect(feature.on).toStrictEqual(listeners);

		// set new listeners
		const newListeners = {
			cancel: () => {}
		};

		feature.set({
			component: "k-test",
			on: {
				...newListeners,
				ignoreMe: "foo"
			}
		});

		expect(feature.on).toStrictEqual(newListeners);
	});

	it("should open with state", async () => {
		const feature = Feature(Panel(), "test", defaults());

		const state = {
			component: "k-test-component",
			props: {
				message: "Hello"
			}
		};

		// would only work with a full panel object
		await feature.open(state);

		expect(feature.component).toStrictEqual("k-test-component");
		expect(feature.props.message).toStrictEqual("Hello");
	});

	it("should open with submitter", async () => {
		const feature = Feature(Panel(), "test", defaults());
		const submitter = () => {};

		await feature.open("/some/path", submitter);

		expect(feature.on.submit).toStrictEqual(submitter);
	});

	it("should return the full URL", async () => {
		const feature = Feature(Panel(), "test", defaults());

		// would only work with a full panel object
		feature.set({
			path: "/a/b/c"
		});

		expect(feature.url()).toStrictEqual("/a/b/c");
	});
});
