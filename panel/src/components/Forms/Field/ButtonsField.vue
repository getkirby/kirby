<template>
	<k-field
		:id="id"
		:help="help"
		:input="false"
		:label="label"
		:name="name"
		type="buttons"
		class="k-buttons-field"
	>
		<k-button-group v-if="buttons.length > 0">
			<k-button
				v-for="button in buttons"
				:key="button.link ?? button.dialog ?? button.drawer"
				v-bind="button"
				:dropdown="true"
				variant="filled"
			/>
		</k-button-group>

		<k-empty v-else icon="bars">{{ $t("buttons.empty") }}</k-empty>
	</k-field>
</template>

<script>
import { help, id, label, name } from "@/mixins/props.js";

/**
 * @since 6.0.0
 * @example <k-buttons-field label="Buttons" :buttons="buttons" />
 */
export default {
	mixins: [help, id, label, name],
	props: {
		buttons: {
			type: Array,
			default: () => []
		}
	}
};
</script>

<style>
.k-buttons-field .k-button-group {
	gap: 2px;
}
.k-buttons-field .k-button {
	--button-align: start;
	--button-height: var(--input-height);
	--button-width: 100%;
}
.k-buttons-field .k-button:is(:not([data-theme]), [data-theme$="-icon"]) {
	--button-color-back: var(--item-color-back);
	box-shadow: var(--shadow);
}
.k-buttons-field .k-button .k-button-arrow {
	margin-inline-start: auto;
	transform: rotate(-90deg);
}
</style>
