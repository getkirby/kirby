<template>
	<div
		:data-disabled="disabled"
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
.k-inputbox {
	display: flex;
	align-items: center;
	line-height: var(--input-leading);
	border: 0;
	background: var(--input-color-back);
	border-radius: var(--input-rounded);
	outline: 1px solid var(--input-color-border);
	color: var(--input-color-text);
	min-height: var(--input-height);
	box-shadow: var(--input-shadow);
	font-family: var(--input-font-family);
	font-size: var(--input-font-size);
}
.k-inputbox:focus-within {
	outline: var(--input-outline-focus);
}

/* Disabled state */
.k-inputbox[data-disabled="true"] {
	--input-color-back: var(--color-background);
	--input-color-icon: var(--color-gray-600);
	--input-shadow: none;
}

/* Variant: multiline */
.k-inputbox[data-variant="multiline"] {
	display: block;
}

/* Variant: box */
.k-inputbox[data-variant="box"] {
	--input-color-border: transparent;
	--input-shadow: var(--shadow);
	--input-outline-focus: none;
}
.k-inputbox[data-variant="box"][data-disabled="true"] {
	--input-shadow: none;
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
	--input-outline-focus: none;
}
.k-inputbox[data-variant="choices"] .k-inputbox-element {
	display: block;
}
.k-inputbox[data-variant="choices"] li {
	padding: var(--input-padding);
	background: var(--input-color-back);
	box-shadow: var(--shadow);
	min-height: var(--input-height);
	border-radius: var(--input-rounded);
}
</style>
