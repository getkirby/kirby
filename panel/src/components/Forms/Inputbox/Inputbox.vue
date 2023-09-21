<template>
	<div
		:aria-disabled="disabled"
		:class="`k-${type}-inputbox`"
		:data-invalid="invalid"
		:data-type="type"
		:data-variant="variant"
		class="k-inputbox"
	>
		<slot name="body">
			<slot name="before">
				<k-inputbox-description :text="before" position="before" />
			</slot>
			<slot name="element">
				<k-inputbox-element>
					<slot />
				</k-inputbox-element>
			</slot>
			<slot name="after">
				<k-inputbox-description :text="after" position="after" />
			</slot>
			<slot name="icon">
				<k-inputbox-icon :type="icon" />
			</slot>
		</slot>
	</div>
</template>

<script>
import {
	after,
	before,
	disabled,
	icon,
	invalid,
	type
} from "@/mixins/props.js";

export const props = {
	mixins: [after, before, disabled, icon, invalid, type],
	inheritAttrs: false,
	props: {
		variant: String
	}
};

export default {
	mixins: [props]
};
</script>

<style>
:root {
	--inputbox-color-back: var(--color-white);
	--inputbox-color-border: var(--color-border);
	--inputbox-color-description: var(--color-text-dimmed);
	--inputbox-color-icon: currentColor;
	--inputbox-color-text: currentColor;
	--inputbox-font-family: var(--font-sans);
	--inputbox-font-size: var(--text-sm);
	--inputbox-height: var(--item-height);
	--inputbox-outline-focus: var(--outline);
	--inputbox-padding: var(--spacing-2);
	--inputbox-padding-multiline: 0.475rem var(--inputbox-padding);
	--inputbox-rounded: var(--rounded);
	--inputbox-shadow: none;
}

.k-inputbox {
	display: flex;
	align-items: center;
	line-height: 1;
	border: 0;
	background: var(--inputbox-color-back);
	border-radius: var(--inputbox-rounded);
	outline: 1px solid var(--inputbox-color-border);
	color: var(--inputbox-color-text);
	min-height: var(--inputbox-height);
	box-shadow: var(--inputbox-shadow);
	font-family: var(--inputbox-font-family);
	font-size: var(--inputbox-font-size);
}
.k-inputbox:focus-within {
	outline: var(--inputbox-outline-focus);
}

/* Disabled state */
.k-inputbox[aria-disabled="true"] {
	--inputbox-color-back: var(--color-background);
	--inputbox-color-icon: var(--color-gray-600);
	--inputbox-shadow: none;
}

/* Variant: multiline */
.k-inputbox[data-variant="multiline"] {
	display: block;
}

/* Variant: box */
.k-inputbox[data-variant="box"] {
	--inputbox-color-border: transparent;
	--inputbox-shadow: var(--shadow);
	--inputbox-outline-focus: none;
}

/* Variant: plain */
.k-inputbox[data-variant="plain"] {
	background: none;
	outline: 0;
	box-shadow: none;
}
.k-inputbox[data-variant="plain"]:focus-within {
	outline: 0;
}

/* Variant: choices */
.k-inputbox[data-variant="choices"] {
	outline: 0;
	background: none;
	--inputbox-outline-focus: none;
}
.k-inputbox[data-variant="choices"] .k-inputbox-element {
	display: block;
}
.k-inputbox[data-variant="choices"] li label {
	padding: var(--inputbox-padding);
	background: var(--inputbox-color-back);
	box-shadow: var(--shadow);
	min-height: var(--inputbox-height);
	border-radius: var(--inputbox-rounded);
}
</style>
