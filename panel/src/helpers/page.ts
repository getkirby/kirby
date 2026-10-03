// TODO: Use Button.vue props type once in place
type StatusButton = {
	disabled: boolean;
	icon: string;
	size: string;
	style: string;
	theme: string;
	title: string;
};

// icon, label and theme of the status as defined in the page blueprint
type StatusFlag = {
	icon?: string | null;
	label?: string | null;
	theme?: string | null;
};

const themes: Record<string, string> = {
	draft: "negative",
	unlisted: "info",
	listed: "positive"
};

/**
 * Returns props for a page status button
 * @unstable
 */
export function status(
	status: string,
	disabled: boolean = false,
	flag: StatusFlag = {}
): StatusButton {
	// @ts-expect-error - window.panel has no type yet
	const panel = window.panel;
	const label = flag.label ?? panel.$t("page.status." + status);
	const theme = flag.theme ?? themes[status] ?? "positive";

	const button: StatusButton = {
		disabled: disabled,
		icon: flag.icon ?? "status-" + status,
		size: "xs",
		style: "--icon-size: 15px",
		theme: theme + "-icon",
		title: panel.$t("page.status") + ": " + label
	};

	if (disabled) {
		button.title += ` (${panel.$t("disabled")})`;
	}

	return button;
}

export default {
	status
};
