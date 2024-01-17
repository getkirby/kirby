<template>
	<k-field v-bind="$props" class="k-blocks-field">
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

		<k-blocks
			ref="blocks"
			v-bind="$props"
			@close="opened = $event"
			@open="opened = $event"
			v-on="$listeners"
		/>

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
					@click="$refs.blocks.choose(value.length)"
				/>
			</footer>
		</template>
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
			opened: []
		};
	},
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
			return (
				!this.disabled && !this.isEmpty && !this.isFull && this.hasFieldsets
			);
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
		}
	}
};
</script>

<style>
.k-blocks-field {
	position: relative;
}
.k-blocks-field .k-field-footer {
	display: flex;
	justify-content: center;
}
.k-blocks-field .k-field-footer[data-has-help="true"] {
	justify-content: space-between;
}
</style>
