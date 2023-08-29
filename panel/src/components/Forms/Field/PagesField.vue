<template>
	<k-field v-bind="$props" class="k-pages-field">
		<template #options>
			<k-button-group class="k-field-options">
				<k-button
					v-if="more && !disabled"
					:autofocus="autofocus"
					:icon="btnIcon"
					:text="btnLabel"
					size="xs"
					variant="filled"
					class="k-field-options-button"
					@click="open"
				/>
			</k-button-group>
		</template>

		<k-collection
			v-bind="collection"
			@empty="open"
			@sort="onInput"
			@sortChange="$emit('change', $event)"
		>
			<template #options="{ index }">
				<k-button
					v-if="!disabled"
					:title="$t('remove')"
					icon="remove"
					@click="remove(index)"
				/>
			</template>
		</k-collection>
	</k-field>
</template>

<script>
import Picker from "@/mixins/forms/picker.js";

export default {
	mixins: [Picker],
	dialog: "k-pages-dialog",
	computed: {
		emptyProps() {
			return {
				icon: "page",
				text: this.empty ?? this.$t("field.pages.empty")
			};
		}
	}
};
</script>

<style>
.k-pages-field[data-disabled="true"] .k-item * {
	pointer-events: all !important;
}
</style>
