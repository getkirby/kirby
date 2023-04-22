import Feature, { defaults } from "./feature.js";

export default (panel) => {
	const parent = Feature(panel, "dropdown", defaults());

	return {
		...parent,

		close() {
			this.emit("close");
			this.reset();
		},

		options() {
			// return an empty array for invalid/non-existing options
			if (Array.isArray(this.props.options) === false) {
				return [];
			}

			return this.props.options.map((option) => {
				if (!option.dialog) {
					return option;
				}

				option.click = () => {
					const url =
						typeof option.dialog === "string"
							? option.dialog
							: option.dialog.url;
					const options =
						typeof option.dialog === "object" ? option.dialog : {};
					return panel.app.$dialog(url, options);
				};

				return option;
			});
		},
		set(state) {
			// deprecated dropdown responses only return the options
			if (state.options) {
				state.props = {
					options: state.options
				};
			}

			return parent.set.call(this, state);
		}
	};
};
