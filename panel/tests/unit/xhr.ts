/**
 * Minimal controllable XMLHttpRequest mock
 * to fire load/error/progress events manually
 */
export class MockXHR {
	static instances: MockXHR[] = [];
	aborted = false;
	body: unknown = null;
	headers: Record<string, string> = {};
	listeners: Record<string, (event?: unknown) => void> = {};
	method = "";
	response: Blob | null = null;
	responseHeaders = "";
	responseType = "";
	responseURL = "";
	status = 0;
	statusText = "";
	upload = {
		listeners: {} as Record<string, (event: unknown) => void>,
		addEventListener(event: string, cb: (event: unknown) => void) {
			this.listeners[event] = cb;
		}
	};
	url = "";

	constructor() {
		MockXHR.instances.push(this);
	}

	abort(): void {
		this.aborted = true;
	}
	addEventListener(event: string, cb: () => void): void {
		this.listeners[event] = cb;
	}
	getAllResponseHeaders(): string {
		return this.responseHeaders;
	}
	open(method: string, url: string): void {
		this.method = method;
		this.url = url;
	}
	send(body: unknown): void {
		this.body = body;
	}
	setRequestHeader(name: string, value: string): void {
		this.headers[name.toLowerCase()] = value;
	}

	emitError(): void {
		this.listeners.error?.();
	}
	emitLoad(text: string, status = 200, headers = "", url = this.url): void {
		this.response = new Blob([text]);
		this.responseHeaders = headers;
		this.responseURL = url;
		this.status = status;
		this.statusText = status === 200 ? "OK" : "";
		this.listeners.load?.();
	}
	emitProgress(loaded: number, total: number, lengthComputable = true): void {
		this.upload.listeners.progress?.({ loaded, total, lengthComputable });
	}
}

export const flush = () => new Promise((resolve) => setTimeout(resolve, 0));
