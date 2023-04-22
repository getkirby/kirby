export function status(status, disabled = false) {
	const button = {
		class: "k-status-icon",
		icon: "status-" + status,
		title:
			window.panel.$t("page.status") +
			": " +
			window.panel.$t("page.status." + status),
		disabled: disabled,
		size: "xs",
		style: "--icon-size: 13px"
	};

	if (disabled) {
		button.theme = "passive";
		button.title += ` (${window.panel.$t("disabled")})`;
	} else {
		if (status === "draft") {
			button.theme = "negative";
		} else if (status === "unlisted") {
			button.theme = "info";
		} else {
			button.theme = "positive";
		}
	}

	return button;
}

export default {
	status
};
