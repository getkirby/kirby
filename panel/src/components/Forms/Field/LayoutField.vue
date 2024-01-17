<template>
	<k-field v-bind="$props" class="k-layout-field">
		<template v-if="!disabled && hasFieldsets" #options>
			<k-button-group layout="collapsed">
				<k-button
					:autofocus="autofocus"
					:text="$t('add')"
					icon="add"
					variant="filled"
					size="xs"
					@click="$refs.layouts.select(0)"
				/>
				<k-button
					icon="dots"
					variant="filled"
					size="xs"
					@click="$refs.options.toggle()"
				/>
				<k-dropdown-content ref="options" :options="options" align-x="end" />
			</k-button-group>
		</template>

		<k-layouts ref="layouts" v-bind="$props" @input="$emit('input', $event)" />

		<template #footer>
			<footer
				v-if="hasFooter"
				:data-has-help="Boolean(help)"
				class="k-field-footer"
			>
				<k-text v-if="help" class="k-help k-field-help" :html="help" />
				<k-button
					v-if="hasMoreButton"
					:title="$t('add')"
					icon="add"
					size="xs"
					variant="filled"
					@click="$refs.layouts.select(value.length)"
				/>
			</footer>
		</template>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as LayoutsProps } from "@/components/Forms/Layouts/Layouts.vue";
import { autofocus } from "@/mixins/props.js";

export default {
	mixins: [FieldProps, LayoutsProps, autofocus],
	inheritAttrs: false,
	computed: {
		hasFieldsets() {
			return this.$helper.object.length(this.fieldsets) > 0;
		},
		hasFooter() {
			if (this.help) {
				return true;
			}

			return this.hasMoreButton;
		},
		hasMoreButton() {
			return !this.disabled && !this.isEmpty && this.hasFieldsets;
		},
		isEmpty() {
			return this.value.length === 0;
		},
		options() {
			return [
				{
					click: () => this.$refs.layouts.copy(),
					disabled: this.isEmpty,
					icon: "template",
					text: this.$t("copy.all")
				},
				{
					click: () => this.$refs.layouts.pasteboard(),
					icon: "download",
					text: this.$t("paste")
				},
				"-",
				{
					click: () => this.$refs.layouts.removeAll(),
					disabled: this.isEmpty,
					icon: "trash",
					text: this.$t("delete.all")
				}
			];
		}
	}
};
</script>

<style>
.k-layout-field .k-field-footer {
	display: flex;
	justify-content: center;
}
.k-layout-field .k-field-footer[data-has-help="true"] {
	display: flex;
	justify-content: space-between;
}
</style>
