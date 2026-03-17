import { random } from "./string";

type UploadParams = {
	/** Signal to abort the upload request */
	abort?: AbortSignal;
	/** Additional attributes added to the FormData */
	attributes?: Record<string, string>;
	/** Callback when upload completed */
	complete?: () => void;
	/** Callback when upload failed */
	error?: (xhr: XMLHttpRequest, file: File, response: unknown) => void;
	/** FormData field name for the file */
	field?: string;
	/** Filename to use for the upload */
	filename?: string;
	/** Request headers */
	headers?: Record<string, string>;
	/** HTTP method (e.g. "POST") */
	method?: string;
	/** Callback whenever the progress changes */
	progress?: (xhr: XMLHttpRequest, file: File, percent: number) => void;
	/** Callback when upload succeeded */
	success?: (xhr: XMLHttpRequest, file: File, response: unknown) => void;
	/** URL endpoint to send the request to */
	url?: string;
};

/**
 * Uploads a file using XMLHttpRequest
 *
 * @param file - File to upload
 * @param params - Upload options
 */
export async function upload(
	file: File,
	params: UploadParams
): Promise<unknown> {
	return new Promise((resolve, reject) => {
		const defaults: Required<UploadParams> = {
			url: "/",
			field: "file",
			method: "POST",
			filename: file.name,
			headers: {},
			attributes: {},
			complete: () => {},
			error: () => {},
			success: () => {},
			progress: () => {},
			abort: new AbortController().signal
		};

		const options = Object.assign(defaults, params);
		const xhr = new XMLHttpRequest();
		const data = new FormData();

		// add file to form data
		data.append(options.field, file, options.filename);

		// add all other attributes to form data
		for (const attribute in options.attributes) {
			const value = options.attributes[attribute];

			if (value !== null && value !== undefined) {
				data.append(attribute, value);
			}
		}

		const progress = (event: ProgressEvent) => {
			if (event.lengthComputable && options.progress) {
				const percent = Math.max(
					0,
					Math.min(100, Math.ceil((event.loaded / event.total) * 100))
				);
				options.progress(xhr, file, percent);
			}
		};

		xhr.upload.addEventListener("loadstart", progress);
		xhr.upload.addEventListener("progress", progress);

		xhr.addEventListener("load", (event: Event) => {
			const target = event.target as XMLHttpRequest;
			let response: { status: string; message?: string };

			try {
				response = JSON.parse(target.response);
			} catch {
				response = {
					status: "error",
					message: "The file could not be uploaded"
				};
			}

			if (response.status === "error") {
				options.error(xhr, file, response);
				reject(response);
			} else {
				options.progress(xhr, file, 100);
				options.success(xhr, file, response);
				resolve(response);
			}
		});

		xhr.addEventListener("error", (event: Event) => {
			const target = event.target as XMLHttpRequest;
			const response = JSON.parse(target.response);

			options.progress(xhr, file, 100);
			options.error(xhr, file, response);
			reject(response);
		});

		xhr.open(options.method, options.url, true);

		// add all request headers
		for (const header in options.headers) {
			xhr.setRequestHeader(header, options.headers[header]);
		}

		// abort the XHR when abort signal is triggered
		options.abort?.addEventListener("abort", () => {
			xhr.abort();
		});

		xhr.send(data);
	});
}

/**
 * Uploads a file in chunks
 *
 * @param file - File to upload
 * @param params - Upload options
 * @param size - chunk size in bytes (default: 5 MB)
 */
export async function uploadAsChunks(
	file: File,
	params: UploadParams,
	size: number = 5242880
): Promise<unknown> {
	const parts = Math.ceil(file.size / size);
	const id = random(4).toLowerCase();
	let response: unknown;

	for (let i = 0; i < parts; i++) {
		// break if upload got aborted in the meantime
		if (params.abort?.aborted) {
			break;
		}

		// slice chunk at the right positions
		const start = i * size;
		const end = Math.min(start + size, file.size);
		const chunk = parts > 1 ? file.slice(start, end, file.type) : file;

		// when more than one part, add flag to
		// recognize chunked upload and its last chunk
		if (parts > 1) {
			params.headers = {
				...params.headers,
				"Upload-Length": String(file.size),
				"Upload-Offset": String(start),
				"Upload-Id": id
			};
		}

		response = await upload(chunk as File, {
			...params,
			// calculate the total progress based on chunk progress
			progress: (xhr, chunk, percent) => {
				const progress = chunk.size * (percent / 100);
				const total = (start + progress) / file.size;
				params.progress?.(xhr, file, Math.round(total * 100));
			}
		});
	}

	return response;
}

export default upload;
