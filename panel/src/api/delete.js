export default (api) => {
	return async (path, data, options, silent = false) => {
		return api.post(path, data, options, "DELETE", silent);
	};
};
