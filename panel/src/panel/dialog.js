import Island, { defaults } from "./island.js";

export default (panel) => {
	// shortcut to submit dialogs
	panel.events.on("keydown.cmd.s", (e) => {
		if (panel.context === "dialog") {
			e.preventDefault();
			panel.dialog.submit();
		}
	});

	return Island(panel, "dialog", defaults());
};
