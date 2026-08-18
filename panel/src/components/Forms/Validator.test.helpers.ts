import { wrap } from "@/helpers/array";
import type Validator from "./Validator";

export function input(attrs: Record<string, string> = {}): HTMLInputElement {
	const element = document.createElement("input");

	for (const [key, value] of Object.entries(attrs)) {
		element.setAttribute(key, value);
	}

	return element;
}

export function mounter<T extends Validator>(
	tag: string,
	validator: CustomElementConstructor
) {
	HTMLElement.prototype.attachInternals ??= function () {
		return {
			setValidity: () => {},
			checkValidity: () => true,
			reportValidity: () => true,
			form: null,
			validity: {},
			validationMessage: "",
			willValidate: true
		} as ElementInternals;
	};

	customElements.define(tag, validator);

	return function mount(
		attrs: Record<string, string> = {},
		children: HTMLElement | HTMLElement[] = []
	): T {
		const element = document.createElement(tag) as T;

		for (const [key, value] of Object.entries(attrs)) {
			element.setAttribute(key, value);
		}

		for (const child of wrap(children)) {
			element.appendChild(child);
		}

		document.body.appendChild(element);

		return element;
	};
}
