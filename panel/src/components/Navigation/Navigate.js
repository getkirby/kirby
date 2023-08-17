export default class Navigate extends HTMLElement {
	static get observedAttributes() {
		return ["axis", "disabled"];
	}

	attributeChangedCallback(attribute, oldValue, newValue) {
		switch (attribute) {
			case "disabled":
				return (this.disabled = newValue);
			case "axis":
				return (this.keys = this.handlers(newValue));
		}
	}

	connectedCallback() {
		this.addEventListener("keydown", this.keydown);
		this.keys = this.handlers(this.getAttribute("axis"));
	}

	disconnectedCallback() {
		this.removeEventListener("keydown", this.keydown);
	}

	elements() {
		return Array.from(
			this.querySelectorAll(
				this.getAttribute("select") ?? ":where(button, a):not(:disabled)"
			)
		);
	}

	focus(index = 0, event) {
		this.move(index, event);
	}

	handlers(axis) {
		switch (axis) {
			case "x":
				return {
					ArrowLeft: this.prev,
					ArrowRight: this.next
				};
			case "y":
				return {
					ArrowUp: this.prev,
					ArrowDown: this.next
				};
			default:
				return {
					ArrowLeft: this.prev,
					ArrowRight: this.next,
					ArrowUp: this.prev,
					ArrowDown: this.next
				};
		}
	}

	keydown(event) {
		if (this.disabled) {
			return false;
		}

		this.keys[event.key]?.apply(this, [event]);
	}

	move(next = 0, event) {
		const elements = this.elements();
		let index = elements.indexOf(document.activeElement);

		if (index === -1) {
			index = 0;
		}

		switch (next) {
			case "first":
				next = 0;
				break;
			case "next":
				next = index + 1;
				break;
			case "last":
				next = elements.length - 1;
				break;
			case "prev":
				next = index - 1;
				break;
		}

		event?.preventDefault();

		elements[next]?.focus();
	}

	next(event) {
		this.move("next", event);
	}

	prev(event) {
		this.move("prev", event);
	}
}
