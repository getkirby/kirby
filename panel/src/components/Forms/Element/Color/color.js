import colors from "@/libraries/colors.js";

export default class extends HTMLElement {
	constructor() {
		super();

		this.color = {
			h: 0,
			s: 1,
			v: 1,
			a: 1
		};
	}

	connectedCallback() {
		this.innerHTML = ``;
		this.classList.add("k-color");

		this.coords = document.createElement("k-coords");
		this.coords.addEventListener("input", this.onCoords.bind(this));
		this.appendChild(this.coords);

		this.hue = document.createElement("input");
		this.hue.type = "range";
		this.hue.min = 0;
		this.hue.max = 360;
		this.hue.value = this.color.h;
		this.hue.setAttribute("data-variant", "hue");
		this.hue.addEventListener("input", this.onHue.bind(this));
		this.appendChild(this.hue);

		if (this.hasAlpha) {
			this.alpha = document.createElement("input");
			this.alpha.type = "range";
			this.alpha.min = 0;
			this.alpha.max = 1;
			this.alpha.step = 0.01;
			this.alpha.value = this.color.a;
			this.alpha.setAttribute("data-variant", "alpha");
			this.alpha.addEventListener("input", this.onAlpha.bind(this));
			this.appendChild(this.alpha);
		}

		this.coords.value = { x: 0, y: 0 };
	}

	between(value, min, max) {
		return Math.min(Math.max(value, min), max);
	}

	get hex() {
		return colors.toString(this.color, "hex");
	}

	get hsl() {
		return colors.toString(this.color, "hsl");
	}

	get hasAlpha() {
		return this.getAttribute("alpha", "true") === "true";
	}

	onAlpha(event) {
		this.onInput(event, {
			a: Number(event.target.value)
		});
	}

	onCoords(event) {
		const x = Math.round(event.target.value.x);
		const y = Math.round(event.target.value.y);

		this.onInput(event, {
			s: x / 100,
			v: 1 - y / 100
		});
	}

	onHue(event) {
		this.onInput(event, {
			h: Number(event.target.value)
		});
	}

	onInput(event, value) {
		event.stopPropagation();
		this.value = value;

		this.dispatchEvent(
			new CustomEvent("input", {
				detail: this.color
			})
		);
	}

	get rgb() {
		return colors.toString(this.color, "rgb");
	}

	get value() {
		return this.color;
	}

	set value(value) {
		if (typeof value === "string") {
			const parsed = colors.parseAs(value, "hsv");

			if (!parsed) {
				return;
			}

			value = parsed;
		}

		value = {
			...this.color,
			...value
		};

		this.color = {
			h: this.between(value.h, 0, 360),
			s: this.between(value.s, 0, 1),
			v: this.between(value.v, 0, 1),
			a: this.hasAlpha ? this.between(value.a, 0, 1) : 1
		};

		const hsl = colors.convert(this.color, "hsl");

		this.style.setProperty("--h", hsl.h);
		this.style.setProperty("--s", (hsl.s * 100).toFixed() + "%");
		this.style.setProperty("--l", (hsl.l * 100).toFixed() + "%");

		this.coords.value = {
			x: this.color.s * 100,
			y: (1 - this.color.v) * 100
		};
		this.hue.value = this.color.h;

		if (this.hasAlpha) {
			this.style.setProperty("--a", hsl.a);
			this.alpha.value = this.color.a;
		}
	}
}
