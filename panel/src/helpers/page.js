/**
 * Returns props for a page status button
 * @unstable
 *
 * @param {string} status
 * @param {boolean} disabled
 * @returns {object}
 */
export function status(status, disabled = false) {
	const button = {
		icon: "status-" + status,
		title:
			window.panel.$t("page.status") +
			": " +
			window.panel.$t("page.status." + status),
		disabled: disabled,
		size: "xs",
		style: "--icon-size: 15px"
	};

	if (disabled) {
		button.title += ` (${window.panel.$t("disabled")})`;
	}

	if (status === "draft") {
		button.theme = "negative-icon";
	} else if (status === "unlisted") {
		button.theme = "info-icon";
	} else {
		button.theme = "positive-icon";
	}

	return button;
}

export default {
	status
};
