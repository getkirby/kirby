import Api from "@/api/index.js";

/**
 * Panel API Setup
 *
 * @todo All of this could be put directly into the api object
 * to avoid the additional configuration code. We don't ship it
 * as a stand-alone library anyway.
 *
 * @param {object} panel
 */
export default (panel) => {
	const api = Api({
		config: {
			endpoint: panel.urls.api,
			onComplete: (requestId) => {
				api.requests = api.requests.filter((value) => {
					return value !== requestId;
				});

				if (api.requests.length === 0) {
					panel.isLoading = false;
				}
			},
			onError: (error) => {
				// handle requests that return no auth
				if (
					error.code === 403 &&
					(error.message === "Unauthenticated" || error.key === "access.panel")
				) {
					panel.open("/logout");
					return false;
				}
			},
			onParserError: ({ html }) => {
				panel.notification.fatal(html);
			},
			onPrepare: (options) => {
				// if language set, add to headers
				if (panel.language.code) {
					options.headers["x-language"] = panel.language.code;
				}

				// add the csrf token to every request
				options.headers["x-csrf"] = panel.system.csrf;

				return options;
			},
			onStart: (requestId, silent = false) => {
				if (silent === false) {
					panel.isLoading = true;
				}

				api.requests.push(requestId);
			},
			onSuccess: () => {
				clearInterval(api.ping);
				api.ping = setInterval(api.auth.ping, 5 * 60 * 1000);
			}
		},
		ping: null,
		requests: []
	});

	// regularly ping API to keep session alive
	api.ping = setInterval(api.auth.ping, 5 * 60 * 1000);

	return api;
};
