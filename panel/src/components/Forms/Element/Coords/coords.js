export default class Coords extends HTMLElement {
	static get observedAttributes() {
		return ["x", "y"];
	}

	constructor() {
		super();

		this.x = 0;
		this.y = 0;
	}

	attributeChangedCallback() {
		this.value = {
			x: this.getAttribute("x") ?? 0,
			y: this.getAttribute("y") ?? 0
		};
	}

	connectedCallback() {
		this.classList.add("k-coords");
		this.tabIndex = 0;

		this.marker = document.createElement("button");
		this.marker.type = "button";
		this.marker.classList.add("k-coords-thumb");

		this.addEventListener("mousedown", (e) => this.onDrag(e));
		this.addEventListener("click", (e) => this.onMove(e));
		this.addEventListener("keydown", (e) => this.onKeys(e));

		this.appendChild(this.marker);

		if (this.value.x !== undefined && this.value.y !== undefined) {
			this.setMarker();
		}
	}

	getCoords(event, bounds) {
		return {
			x: Math.min(Math.max(event.clientX - bounds.left, 0), bounds.width),
			y: Math.min(Math.max(event.clientY - bounds.top, 0), bounds.height)
		};
	}

	get disabled() {
		return (
			this.hasAttribute("aria-disabled") &&
			this.getAttribute("aria-disabled") !== false
		);
	}

	onDrag(e) {
		// only react on mousedown of main mouse button
		if (e.button !== 0) {
			return;
		}

		const moving = (e) => this.onMove(e);
		const end = () => {
			window.removeEventListener("mousemove", moving);
			window.removeEventListener("mouseup", end);
		};
		window.addEventListener("mousemove", moving);
		window.addEventListener("mouseup", end);
	}

	onMove(e) {
		const bounds = this.getBoundingClientRect();
		const coords = this.getCoords(e, bounds);

		const x = (coords.x / bounds.width) * 100;
		const y = (coords.y / bounds.height) * 100;

		this.onInput(e, { x, y });
	}

	onKeys(e) {
		const step = e.shiftKey ? 10 : 1;
		const keys = {
			ArrowUp: { y: this.y - step },
			ArrowDown: { y: this.y + step },
			ArrowLeft: { x: this.x - step },
			ArrowRight: { x: this.x + step }
		};

		if (keys[e.key]) {
			this.onInput(e, keys[e.key]);
		}
	}

	onInput(e, value) {
		e.preventDefault();
		e.stopPropagation();

		if (this.disabled) {
			return false;
		}

		this.value = {
			...this.value,
			...value
		};

		this.dispatchEvent(
			new CustomEvent("input", {
				detail: this.value
			})
		);
	}

	parseValue(value) {
		if (typeof value === "object") {
			return value;
		}

		const keywords = {
			"top left": { x: 0, y: 0 },
			"top center": { x: 50, y: 0 },
			"top right": { x: 100, y: 0 },
			"center left": { x: 0, y: 50 },
			center: { x: 50, y: 50 },
			"center center": { x: 50, y: 50 },
			"center right": { x: 100, y: 50 },
			"bottom left": { x: 0, y: 100 },
			"bottom center": { x: 50, y: 100 },
			"bottom right": { x: 100, y: 100 }
		};

		if (keywords[value]) {
			return keywords[value];
		}

		const coords = value.split(",").map((coord) => coord.trim());

		return {
			x: coords[0],
			y: coords[1] ?? 0
		};
	}

	get value() {
		return {
			x: this.x,
			y: this.y
		};
	}

	set value(value) {
		if (typeof value !== "object") {
			value = this.parseValue(value);
		}

		this.x = Math.min(Math.max(parseFloat(value.x ?? 0), 0), 100);
		this.y = Math.min(Math.max(parseFloat(value.y ?? 0), 0), 100);

		this.setMarker();
	}

	setMarker() {
		if (this.marker) {
			this.marker.style.left = this.x + "%";
			this.marker.style.top = this.y + "%";
		}
	}
}
