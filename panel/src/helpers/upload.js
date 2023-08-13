export default async (file, params) => {
	return new Promise((resolve, reject) => {
		const defaults = {
			url: '/',
			field: 'file',
			method: 'POST',
			filename: file.name,
			attributes: {},
			progress: function() {
			},
		};

		const options = Object.assign(defaults, params);
		const formData = new FormData();

		formData.append(options.field, file, options.filename);

		if (options.attributes) {
			Object.keys(options.attributes).forEach((key) => {
				formData.append(key, options.attributes[key]);
			});
		}

		const xhr = new XMLHttpRequest();

		const progress = (event) => {
			if (!event.lengthComputable || !options.progress) {
				return;
			}

			let percent = Math.max(
				0,
				Math.min(100, (event.loaded / event.total) * 100),
			);

			options.progress(xhr, file, Math.ceil(percent));
		};

		xhr.upload.addEventListener('loadstart', progress);
		xhr.upload.addEventListener('progress', progress);

		xhr.addEventListener('load', (event) => {
			let response = null;

			try {
				response = JSON.parse(event.target.response);
			} catch (e) {
				response = {status: 'error', message: 'The file could not be uploaded'};
			}

			if (response.status === 'error') {
				reject({xhr, file, response});
			} else {
				resolve({xhr, file, response});
				options.progress(xhr, file, 100);
			}
		});

		xhr.addEventListener('error', (event) => {
			const response = JSON.parse(event.target.response);

			reject(xhr, file, response);
			options.progress(xhr, file, 100);
		});

		xhr.open(options.method, options.url, true);

		// add all request headers
		if (options.headers) {
			Object.keys(options.headers).forEach((header) => {
				const value = options.headers[header];
				xhr.setRequestHeader(header, value);
			});
		}

		xhr.send(formData);
	});
};
