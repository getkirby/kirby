<template>
	<k-lab-form>
		<k-lab-examples>
			<k-lab-example label="Checkboxes">
				<div class="k-field">
					<k-label for="test">Checkboxes</k-label>
					<checkbox-input
						id="test"
						min="1"
						max="2"
						name="test"
						required
						:value="value"
						@input="input"
					>
						<label>
							<input type="checkbox" value="a" />
							Option A
						</label>
						<label>
							<input type="checkbox" value="b" />
							Option B
						</label>
						<label>
							<input type="checkbox" value="c" />
							Option C
						</label>
					</checkbox-input>
					<br />
					<br />
					<k-code>{{ value }}</k-code>
				</div>
			</k-lab-example>
		</k-lab-examples>
	</k-lab-form>
</template>

<script>
class CheckboxesInput extends HTMLElement {
	// Identify the element as a form-associated custom element
	static formAssociated = true;

	constructor() {
		super();
		// Get access to the internal form control APIs
		this.internals = this.attachInternals();
		this.entries = [];
	}

	checkValidity() {
		return this.internals.checkValidity();
	}

	commit(input) {
		if (input.checked) {
			this.entries.push(input.value);
		} else {
			this.entries = this.entries.filter((item) => item !== input.value);
		}
	}

	connectedCallback() {
		this.tabIndex = 0;
		this.validate();

		this.entries = this.getAttribute("value").split(",");

		this.querySelectorAll("input").forEach((input) => {
			input.checked = this.entries.includes(input.value);

			input.addEventListener("input", (event) => {
				event.stopPropagation();

				this.commit(input);
				this.internals.setFormValue(this.entries);

				this.validate();
				this.emit();
			});
		});
	}

	emit() {
		const inputEvent = new InputEvent("input", {
			data: this.entries
		});

		this.dispatchEvent(inputEvent);
	}

	// The following properties and methods aren't strictly required,
	// but browser-level form controls provide them. Providing them helps
	// ensure consistency with browser-provided controls.
	get form() {
		return this.internals.form;
	}

	get name() {
		return this.getAttribute("name");
	}

	reportValidity() {
		return this.internals.reportValidity();
	}

	get type() {
		return this.localName;
	}

	validate() {
		const max = parseInt(this.getAttribute("max"));
		const min = parseInt(this.getAttribute("min"));

		const required =
			this.hasAttribute("required") &&
			this.getAttribute("required") !== "false";

		const anchor = this.querySelector("input[type=checkbox]");

		if (required && this.entries.length === 0) {
			this.internals.setValidity(
				{ valueMissing: true },
				`Please select at least one option`,
				anchor
			);
		} else if (this.hasAttribute("min") && this.entries.length < min) {
			this.internals.setValidity(
				{ rangeUnderflow: true },
				`Please select at least ${min} options`,
				anchor
			);
		} else if (this.hasAttribute("max") && this.entries.length > max) {
			this.internals.setValidity(
				{ rangeOverflow: true },
				`Please select no more than ${max} options`,
				anchor
			);
		} else {
			this.internals.setValidity({});
		}
	}

	get validity() {
		return this.internals.validity;
	}

	get validationMessage() {
		return this.internals.validationMessage;
	}

	// Form controls usually expose a "value" property
	get value() {
		return this.entries;
	}

	set value(value) {
		this.entries = value;
	}

	get willValidate() {
		return this.internals.willValidate;
	}
}

if (!customElements.get("checkbox-input")) {
	customElements.define("checkbox-input", CheckboxesInput);
}

export default {
	data() {
		return {
			value: ["a"]
		};
	},
	methods: {
		input(event) {
			this.value = event.target.value;
		}
	}
};
</script>

<style>
.k-field:has(:invalid) > label {
	color: var(--color-red-600);
}

checkbox-input {
	display: block;
	border-radius: var(--rounded);
}
checkbox-input:focus {
	outline: var(--outline);
}
checkbox-input > label {
	display: flex;
	align-items: center;
	gap: 0.5rem;
	padding: 0.5rem 0.5rem;
	margin-bottom: 2px;
	background: var(--color-white);
	box-shadow: var(--shadow);
	border-radius: var(--rounded);
}
</style>
