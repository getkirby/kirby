import { random } from "./string.js";

/**
 * Uploads a file using XMLHttpRequest.
 *
 * @param {File} file - file to upload
 * @param {Object} params - upload options:
 * @param {string} params.url - URL endpoint to sent the request to
 * @param {string} params.method - HTTP method (e.g. "POST")
 * @param {string} params.filename - filename to use for the upload
 * @param {Object} params.headers - request headers
 * @param {Object} params.attributes - additional attributes
 * @param {AbortSignal} params.abort - signal to abort the upload request
 * @param {Function} params.progress - callback whenever the progress changes
 * @param {Function} params.complete - callback when upload completed
 * @param {Function} params.success - callback when upload succeeded
 * @param {Function} params.error - callback when upload failed
 */
export async function upload(file, params) {
	return new Promise((resolve, reject) => {
		const defaults = {
			url: "/",
			field: "file",
			method: "POST",
			filename: file.name,
			headers: {},
			attributes: {},
			complete: () => {},
			error: () => {},
			success: () => {},
			progress: () => {}
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

		const progress = (event) => {
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

		xhr.addEventListener("load", (event) => {
			let response = null;

			try {
				response = JSON.parse(event.target.response);
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

		xhr.addEventListener("error", (event) => {
			const response = JSON.parse(event.target.response);

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
 * @param {File} file - file to upload
 * @param {Object} params - upload options (see `upload` method for details)
 * @param {number} size - chunk size in bytes (default: 5 MB)
 */
export async function uploadAsChunks(file, params, size = 5242880) {
	const parts = Math.ceil(file.size / size);
	const id = random(4).toLowerCase();
	let response;

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
				"Upload-Length": file.size,
				"Upload-Offset": start,
				"Upload-Id": id
			};
		}

		response = await upload(chunk, {
			...params,
			// calculate the total progress based on chunk progress
			progress: (xhr, chunk, percent) => {
				const progress = chunk.size * (percent / 100);
				const total = (start + progress) / file.size;
				params.progress(xhr, file, Math.round(total * 100));
			}
		});
	}

	return response;
}

export default upload;
