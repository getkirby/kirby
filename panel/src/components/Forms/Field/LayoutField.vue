<template>
	<k-field
		v-bind="$props"
		:class="['k-layout-field', $attrs.class]"
		:style="$attrs.style"
	>
		<template v-if="!disabled && hasFieldsets" #options>
			<k-button-group layout="collapsed">
				<k-button
					:autofocus="autofocus"
					:text="$t('add')"
					icon="add"
					variant="filled"
					size="xs"
					class="input-focus"
					@click="$refs.layouts.select(0)"
				/>
				<k-button
					icon="dots"
					variant="filled"
					size="xs"
					@click="$refs.options.toggle()"
				/>
				<k-dropdown ref="options" :options="options" align-x="end" />
			</k-button-group>
		</template>

		<k-input-validator
			v-bind="{ min, max, required }"
			:value="JSON.stringify(value)"
		>
			<k-layouts
				ref="layouts"
				v-bind="$props"
				@input="$emit('input', $event)"
			/>
		</k-input-validator>

		<footer v-if="!disabled && hasFieldsets">
			<k-button
				:title="$t('add')"
				icon="add"
				size="xs"
				variant="filled"
				@click="$refs.layouts.select(value.length)"
			/>
		</footer>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as LayoutsProps } from "@/components/Forms/Layouts/Layouts.vue";
import { autofocus } from "@/mixins/props.js";

export default {
	mixins: [FieldProps, LayoutsProps, autofocus],
	inheritAttrs: false,
	emits: ["input"],
	computed: {
		hasFieldsets() {
			return this.$helper.object.length(this.fieldsets) > 0;
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
.k-layout-field > footer {
	display: flex;
	justify-content: center;
	margin-top: var(--spacing-3);
}
</style>
