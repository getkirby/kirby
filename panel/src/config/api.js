import Api from "@/api/index.js";

export default {
	install(Vue, panel) {
		Vue.prototype.$api = Vue.$api = Api({
			config: {
				endpoint: panel.$urls.api,
				onComplete: (requestId) => {
					Vue.$api.requests = Vue.$api.requests.filter((value) => {
						return value !== requestId;
					});

					if (Vue.$api.requests.length === 0) {
						panel.isLoading = false;
					}
				},
				onError: (error) => {
					// handle requests that return no auth
					if (
						error.code === 403 &&
						(error.message === "Unauthenticated" ||
							error.key === "access.panel")
					) {
						Vue.prototype.$go("/logout");
						return false;
					}

					if (panel.$config.debug) {
						window.console.error(error);
					}
				},
				onParserError: ({ html }) => {
					panel.notification.fatal(html);
				},
				onPrepare: (options) => {
					// if language set, add to headers
					if (panel.$language) {
						options.headers["x-language"] = panel.$language.code;
					}

					// add the csrf token to every request
					options.headers["x-csrf"] = panel.$system.csrf;

					return options;
				},
				onStart: (requestId, silent = false) => {
					if (silent === false) {
						panel.isLoading = true;
					}

					Vue.$api.requests.push(requestId);
				},
				onSuccess: () => {
					clearInterval(Vue.$api.ping);
					Vue.$api.ping = setInterval(Vue.$api.auth.ping, 5 * 60 * 1000);
				}
			},
			ping: null,
			requests: []
		});

		// regularly ping API to keep session alive
		Vue.$api.ping = setInterval(Vue.$api.auth.ping, 5 * 60 * 1000);
	}
};
