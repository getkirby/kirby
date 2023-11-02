export default class Autosize extends HTMLElement {
	connectedCallback() {
		// the autosize component needs to be a block
		// element for the resize observer to work
		this.style.display = "block";

		// collect all autosizing textareas
		this.textareas = this.querySelectorAll("textarea");

		// activate autosizing for all textareas
		for (const textarea of this.textareas) {
			// required styles
			textarea.style.resize = "none";
			textarea.style.overflowY = "hidden";

			// attach custom autosize method to textarea
			textarea.autosize = () => {
				textarea.style.height = "auto";
				textarea.style.height = textarea.scrollHeight + "px";
			};

			// trigger resize on input
			textarea.addEventListener("input", () => textarea.autosize());
		}

		// resize all textareas when the container size changes
		this.resizer = new ResizeObserver(() => {
			for (const textarea of this.textareas) {
				textarea.autosize();
			}
		});

		this.resizer.observe(this);
	}

	disconnectedCallback() {
		this.resizer.unobserve(this);
	}
}
