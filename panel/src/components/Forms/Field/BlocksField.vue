<template>
	<k-field
		v-bind="$props"
		:class="['k-blocks-field', $attrs.class]"
		:style="$attrs.style"
	>
		<template v-if="!disabled && hasFieldsets" #options>
			<k-button-group layout="collapsed">
				<k-button
					:autofocus="autofocus"
					:disabled="isFull"
					:responsive="true"
					:text="$t('add')"
					icon="add"
					variant="filled"
					size="xs"
					class="input-focus"
					@click="$refs.blocks.choose(value.length)"
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

		<k-input-validator
			v-bind="{ min, max, required }"
			:value="JSON.stringify(value)"
		>
			<k-blocks
				ref="blocks"
				v-bind="$props"
				@close="opened = $event"
				@open="opened = $event"
				@input="$emit('input', $event)"
				@collapsible-change="onCollapsibleChange"
			/>
		</k-input-validator>

		<footer v-if="!disabled && !isEmpty && !isFull && hasFieldsets">
			<k-button
				:title="$t('add')"
				icon="add"
				size="xs"
				variant="filled"
				@click="$refs.blocks.choose(value.length)"
			/>
		</footer>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as BlocksProps } from "@/components/Forms/Blocks/Blocks.vue";

export default {
	mixins: [FieldProps, BlocksProps],
	inheritAttrs: false,
	data() {
		return {
			opened: [],
			isCollapsible: false
		};
	},
	computed: {
		hasFieldsets() {
			return this.$helper.object.length(this.fieldsets) > 0;
		},
		isEmpty() {
			return this.value.length === 0;
		},
		isFull() {
			return this.max && this.value.length >= this.max;
		},
		options() {
			return [
				{
					click: () => this.$refs.blocks.copyAll(),
					disabled: this.isEmpty,
					icon: "template",
					text: this.$t("copy.all")
				},
				{
					click: () => this.$refs.blocks.pasteboard(),
					disabled: this.isFull,
					icon: "download",
					text: this.$t("paste")
				},
				...(this.isCollapsible
					? [
							"-",
							{
								click: () => this.$refs.blocks.collapseAll(),
								disabled: this.isEmpty,
								icon: "collapse",
								text: this.$t("collapse.all")
							},
							{
								click: () => this.$refs.blocks.expandAll(),
								disabled: this.isEmpty,
								icon: "expand",
								text: this.$t("expand.all")
							}
						]
					: []),
				"-",
				{
					click: () => this.$refs.blocks.removeAll(),
					disabled: this.isEmpty,
					icon: "trash",
					text: this.$t("delete.all")
				}
			];
		}
	},
	methods: {
		focus() {
			this.$refs.blocks.focus();
		},
		onCollapsibleChange(value) {
			this.isCollapsible = value;
		}
	}
};
</script>

<style>
.k-blocks-field {
	position: relative;
}

.k-blocks-field > footer {
	display: flex;
	justify-content: center;
	margin-top: var(--spacing-3);
}
</style>
