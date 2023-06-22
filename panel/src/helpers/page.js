export function status(status, disabled = false) {
	const button = {
		class: "k-status-icon",
		click: () => window.panel.dialog("/dialogs/" + page.link + "/changeStatus"),
		icon: "status-" + status,
		title:
			window.panel.$t("page.status") +
			": " +
			window.panel.$t("page.status." + status),
		disabled: disabled,
		size: "xs",
		style: "--icon-size: 12px"
	};

	if (disabled) {
		button.title += ` (${window.panel.$t("disabled")})`;
	}

	if (status === "draft") {
		button.theme = "negative";
	} else if (status === "unlisted") {
		button.theme = "info";
	} else {
		button.theme = "positive";
	}

	return button;
}

export default {
	status
};
