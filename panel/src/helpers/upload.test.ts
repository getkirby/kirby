import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { upload, uploadAsChunks } from "./upload";

type LoadListener = (event: Event) => void;
type ProgressListener = (event: ProgressEvent) => void;

class MockXHRUpload {
	listeners: Record<string, ProgressListener> = {};
	addEventListener(event: string, cb: ProgressListener): void {
		this.listeners[event] = cb;
	}
}

/**
 * Minimal controllable XMLHttpRequest mock.
 * Each instance is collected in `MockXHR.instances`
 * so tests can fire load/error/progress events manually.
 */
class MockXHR {
	static instances: MockXHR[] = [];
	upload = new MockXHRUpload();
	listeners: Record<string, LoadListener> = {};
	response = "";
	method = "";
	url = "";
	headers: Record<string, string> = {};
	sent = false;
	aborted = false;

	constructor() {
		MockXHR.instances.push(this);
	}

	addEventListener(event: string, cb: LoadListener): void {
		this.listeners[event] = cb;
	}
	open(method: string, url: string): void {
		this.method = method;
		this.url = url;
	}
	setRequestHeader(key: string, value: string): void {
		this.headers[key] = value;
	}
	send(): void {
		this.sent = true;
	}
	abort(): void {
		this.aborted = true;
	}

	emitLoad(response: string): void {
		this.response = response;
		this.listeners.load?.({ target: this } as unknown as Event);
	}
	emitError(response: string): void {
		this.response = response;
		this.listeners.error?.({ target: this } as unknown as Event);
	}
	emitProgress(loaded: number, total: number, lengthComputable = true): void {
		this.upload.listeners.progress?.({
			loaded,
			total,
			lengthComputable
		} as unknown as ProgressEvent);
	}
}

const flush = () => new Promise((resolve) => setTimeout(resolve, 0));
const makeFile = (content = "content") =>
	new File([content], "test.txt", { type: "text/plain" });

beforeEach(() => {
	MockXHR.instances = [];
	vi.stubGlobal("XMLHttpRequest", MockXHR);
});

afterEach(() => {
	vi.unstubAllGlobals();
	vi.restoreAllMocks();
});

describe("$helper.upload()", () => {
	it("should resolve on a successful response", async () => {
		const success = vi.fn();
		const promise = upload(makeFile(), { url: "/api", success });

		MockXHR.instances[0].emitLoad(JSON.stringify({ status: "ok", value: 1 }));

		await expect(promise).resolves.toEqual({ status: "ok", value: 1 });
		expect(success).toHaveBeenCalled();
	});

	it("should reject when the response status is error", async () => {
		const error = vi.fn();
		const promise = upload(makeFile(), { error });

		MockXHR.instances[0].emitLoad(
			JSON.stringify({ status: "error", message: "nope" })
		);

		await expect(promise).rejects.toEqual({ status: "error", message: "nope" });
		expect(error).toHaveBeenCalled();
	});

	it("should reject with a generic error when the response is not JSON", async () => {
		const promise = upload(makeFile(), {});

		MockXHR.instances[0].emitLoad("<not json>");

		await expect(promise).rejects.toEqual({
			status: "error",
			message: "The file could not be uploaded"
		});
	});

	it("should reject on a request error", async () => {
		const error = vi.fn();
		const promise = upload(makeFile(), { error });

		MockXHR.instances[0].emitError(JSON.stringify({ status: "error" }));

		await expect(promise).rejects.toEqual({ status: "error" });
		expect(error).toHaveBeenCalled();
	});

	it("should report progress", async () => {
		const progress = vi.fn();
		const promise = upload(makeFile(), { progress });

		MockXHR.instances[0].emitProgress(50, 100);
		MockXHR.instances[0].emitLoad(JSON.stringify({ status: "ok" }));

		await promise;
		expect(progress).toHaveBeenCalledWith(
			expect.anything(),
			expect.any(File),
			50
		);
		expect(progress).toHaveBeenCalledWith(
			expect.anything(),
			expect.any(File),
			100
		);
	});

	it("should ignore progress events that are not length-computable", async () => {
		const progress = vi.fn();
		const promise = upload(makeFile(), { progress });

		MockXHR.instances[0].emitProgress(50, 100, false);
		MockXHR.instances[0].emitLoad(JSON.stringify({ status: "ok" }));

		await promise;
		// only the final success progress(…, 100) fires, not the non-computable one
		expect(progress).toHaveBeenCalledTimes(1);
		expect(progress).toHaveBeenCalledWith(
			expect.anything(),
			expect.any(File),
			100
		);
	});

	it("should skip null and undefined attributes", async () => {
		const append = vi.spyOn(FormData.prototype, "append");
		const promise = upload(makeFile(), {
			attributes: {
				a: "1",
				b: null as unknown as string,
				c: undefined as unknown as string
			}
		});

		MockXHR.instances[0].emitLoad(JSON.stringify({ status: "ok" }));
		await promise;

		expect(append).toHaveBeenCalledWith("a", "1");
		expect(append).not.toHaveBeenCalledWith("b", expect.anything());
		expect(append).not.toHaveBeenCalledWith("c", expect.anything());
	});

	it("should set request headers", async () => {
		const promise = upload(makeFile(), { headers: { "X-Test": "1" } });
		const xhr = MockXHR.instances[0];

		expect(xhr.headers).toEqual({ "X-Test": "1" });

		xhr.emitLoad(JSON.stringify({ status: "ok" }));
		await promise;
	});

	it("should abort when the signal fires", async () => {
		const controller = new AbortController();
		const promise = upload(makeFile(), { abort: controller.signal });
		const xhr = MockXHR.instances[0];

		controller.abort();
		expect(xhr.aborted).toBe(true);

		xhr.emitLoad(JSON.stringify({ status: "ok" }));
		await promise;
	});
});

describe("$helper.uploadAsChunks()", () => {
	it("should upload a small file as a single chunk", async () => {
		const promise = uploadAsChunks(makeFile(), { url: "/" });
		await flush();

		expect(MockXHR.instances).toHaveLength(1);
		expect(MockXHR.instances[0].headers["Upload-Id"]).toBeUndefined();

		MockXHR.instances[0].emitLoad(JSON.stringify({ status: "ok" }));
		await expect(promise).resolves.toEqual({ status: "ok" });
	});

	it("should upload a large file in chunks with upload headers", async () => {
		const progress = vi.fn();
		const promise = uploadAsChunks(
			makeFile("abcdefg"),
			{ url: "/", progress },
			2
		);

		for (let i = 0; i < 4; i++) {
			await flush();
			expect(MockXHR.instances).toHaveLength(i + 1);
			expect(MockXHR.instances[i].headers["Upload-Length"]).toBe("7");
			expect(MockXHR.instances[i].headers["Upload-Id"]).toBeDefined();

			MockXHR.instances[i].emitProgress(2, 2);
			MockXHR.instances[i].emitLoad(JSON.stringify({ status: "ok", part: i }));
		}

		await expect(promise).resolves.toEqual({ status: "ok", part: 3 });
		expect(progress).toHaveBeenCalled();
	});

	it("should stop chunking when aborted between chunks", async () => {
		const controller = new AbortController();
		const promise = uploadAsChunks(
			makeFile("abcdefg"),
			{ url: "/", abort: controller.signal },
			2
		);

		await flush();
		expect(MockXHR.instances).toHaveLength(1);

		MockXHR.instances[0].emitLoad(JSON.stringify({ status: "ok" }));
		controller.abort();
		await promise;

		expect(MockXHR.instances).toHaveLength(1);
	});
});
