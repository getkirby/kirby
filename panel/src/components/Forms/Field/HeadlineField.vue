<template>
	<div :class="['k-headline-field', $attrs.class]" :style="$attrs.style">
		<k-headline class="h2">
			{{ label }}
		</k-headline>
		<footer v-if="help" class="k-field-footer">
			<k-text class="k-help k-field-help" :html="help" />
		</footer>
	</div>
</template>

<script>
import { help, label } from "@/mixins/props.js";

/**
 * @example <k-headline-field label="This is a headline" />
 */
export default {
	mixins: [help, label],
	inheritAttrs: false
};
</script>

<style>
.k-headline-field {
	position: relative;
	padding-top: 1.5rem;
}
/* don't add the top padding,
if the headline is the very first form element */
.k-fieldset > .k-grid .k-column:first-child .k-headline-field {
	padding-top: 0;
}

.k-headline-field h2.k-headline {
	font-weight: var(--font-normal);
}

.k-headline-field footer {
	margin-top: var(--spacing-2);
}
</style>
