import Modal, { defaults } from "./modal.js";

export default (panel) => {
	// shortcut to submit dialogs
	panel.events.on("dialog.save", (e) => {
		e?.preventDefault?.();
		panel.dialog.submit();
	});

	return Modal(panel, "dialog", defaults());
};
