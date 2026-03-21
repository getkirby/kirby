// TODO: Use Button.vue props type once in place
type StatusButton = {
	disabled: boolean;
	icon: string;
	size: string;
	style: string;
	theme: string;
	title: string;
};

/**
 * Returns props for a page status button
 * @unstable
 */
export function status(
	status: string,
	disabled: boolean = false
): StatusButton {
	const panel = window.panel;

	const button: StatusButton = {
		disabled: disabled,
		icon: "status-" + status,
		size: "xs",
		style: "--icon-size: 15px",
		theme: "positive-icon",
		title: panel.$t("page.status") + ": " + panel.$t("page.status." + status)
	};

	if (disabled) {
		button.title += ` (${panel.$t("disabled")})`;
	}

	if (status === "draft") {
		button.theme = "negative-icon";
	} else if (status === "unlisted") {
		button.theme = "info-icon";
	}

	return button;
}

export default {
	status
};
