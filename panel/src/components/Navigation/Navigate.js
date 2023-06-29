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

	canGotoNext(element) {
		return (
			!this.isInput(element) || element.selectionEnd === element.value.length
		);
	}

	canGotoPrev(element) {
		return !this.isInput(element) || element.selectionStart === 0;
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
				this.getAttribute("select") || ":where(button, a, input):not(:disabled)"
			)
		);
	}

	focus(index = 0, event) {
		event?.preventDefault();
		this.elements()[index]?.focus();
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

	isInput(element) {
		return element.matches("input");
	}

	keydown(event) {
		if (this.disabled) {
			return false;
		}

		this.keys[event.key]?.apply(this, [event.target, event]);
	}

	move(element, step, event) {
		const elements = this.elements();
		const index = elements.indexOf(element);

		if (index === -1) {
			return false;
		}

		event?.preventDefault();

		elements[index + step]?.focus();
	}

	next(element, event) {
		if (this.canGotoNext(element)) {
			this.move(element, 1, event);
		}
	}

	prev(element, event) {
		if (this.canGotoPrev(element)) {
			this.move(element, -1, event);
		}
	}
}
