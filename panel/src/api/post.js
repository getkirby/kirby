export default (api) => {
	return async (path, data, options, method = "POST", silent = false) => {
		return api.request(
			path,
			Object.assign(options ?? {}, {
				method: method,
				body: JSON.stringify(data)
			}),
			silent
		);
	};
};
