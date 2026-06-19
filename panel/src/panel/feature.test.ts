import { describe, expect, it, vi } from "vitest";
import Feature, { defaults } from "./feature";
import { type Listener } from "./listeners";
import Panel from "./panel";

describe("panel.feature", () => {
	it("should add event listeners", async () => {
		const panel = Panel.create(app);
		const feature = Feature(panel, "test", defaults());
		const listeners = {
			submit: () => {},
			cancel: () => {}
		};

		feature.addEventListeners(listeners);

		expect(feature.on).toStrictEqual(listeners);
	});

	it("should ignore invalid event listeners", async () => {
		const panel = Panel.create(app);
		const feature = Feature(panel, "test", defaults());
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
		const panel = Panel.create(app);
		const feature = Feature(panel, "test", defaults());
		const listeners = {
			submit: () => {}
		};

		feature.addEventListeners(listeners);

		expect(feature.hasEventListener("submit")).toStrictEqual(true);
		expect(feature.hasEventListener("cancel")).toStrictEqual(false);
	});

	it("should emit events", async () => {
		const panel = Panel.create(app);
		const feature = Feature(panel, "test", defaults());
		let emitted = false;

		feature.addEventListeners({
			submit: () => {
				emitted = true;
			}
		});

		feature.emit("submit");

		expect(emitted).toStrictEqual(true);
	});

	describe("get()", () => {
		it("should return the response", async () => {
			const data = { $test: { component: "k-test" } };
			const panel = Panel.create(app);
			vi.spyOn(panel, "get").mockResolvedValue(data);
			const feature = Feature(panel, "test", defaults());

			expect(await feature.get("/some/url")).toStrictEqual(data);
		});

		it("should return false when the request fails", async () => {
			const panel = Panel.create(app);
			vi.spyOn(panel, "get").mockRejectedValue(new Error("Network error"));
			const feature = Feature(panel, "test", defaults());

			expect(await feature.get("/some/url")).toStrictEqual(false);
		});

		it("should reset isLoading after the request", async () => {
			const panel = Panel.create(app);
			const feature = Feature(panel, "test", defaults());
			await feature.get("/some/url");

			expect(feature.isLoading).toStrictEqual(false);
		});

		it("should reset isLoading after a failed request", async () => {
			const panel = Panel.create(app);
			vi.spyOn(panel, "get").mockRejectedValue(new Error());
			const feature = Feature(panel, "test", defaults());
			await feature.get("/some/url");

			expect(feature.isLoading).toStrictEqual(false);
		});
	});

	describe("load()", () => {
		it("should reset isLoading after loading", async () => {
			const panel = Panel.create(app);
			const feature = Feature(panel, "test", defaults());
			await feature.load("/some/path");

			expect(feature.isLoading).toStrictEqual(false);
		});

		it("should not set isLoading when silent", async () => {
			let isLoadingDuringOpen = false;
			const panel = Panel.create(app);
			const feature = Feature(panel, "test", defaults());

			vi.spyOn(panel, "open").mockImplementation(async () => {
				isLoadingDuringOpen = feature.isLoading;
				return undefined;
			});

			await feature.load("/some/path", { silent: true });

			expect(isLoadingDuringOpen).toStrictEqual(false);
		});

		it("should add listeners from options", async () => {
			const panel = Panel.create(app);
			const feature = Feature(panel, "test", defaults());
			const submit = () => {};
			await feature.load("/some/path", { on: { submit } });

			expect(feature.on.submit).toStrictEqual(submit);
		});
	});

	describe("open()", () => {
		it("should open with state", async () => {
			const panel = Panel.create(app);
			const feature = Feature(panel, "test", defaults());

			await feature.open({
				component: "k-test-component",
				props: { message: "Hello" }
			});

			expect(feature.component).toStrictEqual("k-test-component");
			expect(feature.props.message).toStrictEqual("Hello");
		});

		it("should open with submitter", async () => {
			const panel = Panel.create(app);
			const feature = Feature(panel, "test", defaults());
			const submitter = () => {};

			await feature.open("/some/path", submitter);

			expect(feature.on.submit).toStrictEqual(submitter);
		});

		it("should emit open event", async () => {
			const panel = Panel.create(app);
			const feature = Feature(panel, "test", defaults());
			let emitted = false;

			await feature.open(
				{ component: "k-test" },
				{
					on: {
						open: () => {
							emitted = true;
						}
					}
				}
			);

			expect(emitted).toStrictEqual(true);
		});
	});

	describe("post()", () => {
		it("should throw when no path is set", async () => {
			const panel = Panel.create(app);
			const feature = Feature(panel, "test", defaults());
			await expect(feature.post({})).rejects.toThrow(
				"The test cannot be posted"
			);
		});

		it("should return the response", async () => {
			const data = { ok: true };
			const panel = Panel.create(app);
			vi.spyOn(panel, "post").mockResolvedValue(data);
			const feature = Feature(panel, "test", defaults());
			feature.set({ path: "/some/path" });

			expect(await feature.post({})).toStrictEqual(data);
		});

		it("should return false when the request fails", async () => {
			const panel = Panel.create(app);
			vi.spyOn(panel, "post").mockRejectedValue(new Error());
			const feature = Feature(panel, "test", defaults());
			feature.set({ path: "/some/path" });

			expect(await feature.post({})).toStrictEqual(false);
		});

		it("should reset isLoading after the request", async () => {
			const panel = Panel.create(app);
			const feature = Feature(panel, "test", defaults());
			feature.set({ path: "/some/path" });
			await feature.post({});

			expect(feature.isLoading).toStrictEqual(false);
		});

		it("should reset isLoading after a failed request", async () => {
			const panel = Panel.create(app);
			vi.spyOn(panel, "post").mockRejectedValue(new Error());
			const feature = Feature(panel, "test", defaults());
			feature.set({ path: "/some/path" });
			await feature.post({});

			expect(feature.isLoading).toStrictEqual(false);
		});
	});

	describe("refresh()", () => {
		it("should return undefined when the request fails", async () => {
			const panel = Panel.create(app);
			vi.spyOn(panel, "get").mockRejectedValue(new Error());
			const feature = Feature(panel, "test", defaults());
			feature.set({ component: "k-test", path: "/some/path" });

			expect(await feature.refresh()).toBeUndefined();
		});

		it("should return undefined when the component changed", async () => {
			const panel = Panel.create(app);
			vi.spyOn(panel, "get").mockResolvedValue({
				test: { component: "k-other", props: {} }
			});
			const feature = Feature(panel, "test", defaults());
			feature.set({ component: "k-test", path: "/some/path" });

			expect(await feature.refresh()).toBeUndefined();
		});

		it("should update props when the component matches", async () => {
			const panel = Panel.create(app);
			vi.spyOn(panel, "get").mockResolvedValue({
				test: { component: "k-test", props: { message: "updated" } }
			});
			const feature = Feature(panel, "test", defaults());
			feature.set({ component: "k-test", path: "/some/path" });
			await feature.refresh();

			expect(feature.props).toStrictEqual({ message: "updated" });
		});
	});

	describe("reload()", () => {
		it("should return false when no path is set", async () => {
			const panel = Panel.create(app);
			const feature = Feature(panel, "test", defaults());
			expect(await feature.reload()).toStrictEqual(false);
		});

		it("should reload when a path is set", async () => {
			const panel = Panel.create(app);
			const feature = Feature(panel, "test", defaults());
			feature.set({ path: "/some/path" });

			expect(await feature.reload()).not.toStrictEqual(false);
		});
	});

	it("should set state", async () => {
		const panel = Panel.create(app);
		const feature = Feature(panel, "test", defaults());

		feature.set({
			component: "k-test"
		});

		expect(feature.component).toStrictEqual("k-test");
		expect(feature.on).toStrictEqual({});
	});

	it("should set state with event listeners", async () => {
		const panel = Panel.create(app);
		const feature = Feature(panel, "test", defaults());

		const listeners = {
			submit: () => {}
		};

		feature.set({
			component: "k-test",
			on: {
				...listeners,
				ignoreMe: null
			} as unknown as Record<string, Listener>
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
			} as unknown as Record<string, Listener>
		});

		expect(feature.on).toStrictEqual(newListeners);
	});

	it("should return the full URL", async () => {
		const panel = Panel.create(app);
		const feature = Feature(panel, "test", defaults());

		feature.set({
			path: "/a/b/c"
		});

		expect(feature.url().pathname).toStrictEqual("/a/b/c");
	});
});
