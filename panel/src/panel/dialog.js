import Island, { defaults } from "./island.js";

export default (panel) => {
	// shortcut to submit dialogs
	panel.events.on("dialog.save", (e) => {
		if (e?.preventDefault) {
			e.preventDefault();
		}
		panel.dialog.submit();
	});

	return Island(panel, "dialog", defaults());
};
