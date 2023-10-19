<template>
	<k-field v-bind="$props" class="k-blocks-field">
		<template v-if="hasFieldsets" #options>
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
			:autofocus="autofocus"
			:compact="false"
			:empty="empty"
			:endpoints="endpoints"
			:fieldsets="fieldsets"
			:fieldset-groups="fieldsetGroups"
			:group="group"
			:max="max"
			:value="value"
			@close="opened = $event"
			@open="opened = $event"
			v-on="$listeners"
		/>

		<footer
			v-if="!isEmpty && !isFull && hasFieldsets"
			class="k-bar"
			data-align="center"
		>
			<k-button
				icon="add"
				size="xs"
				variant="filled"
				:title="$t('add')"
				@click="$refs.blocks.choose(value.length)"
			/>
		</footer>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";

export default {
	mixins: [Field],
	inheritAttrs: false,
	props: {
		autofocus: Boolean,
		empty: String,
		fieldsets: Object,
		fieldsetGroups: Object,
		group: String,
		max: {
			type: Number,
			default: null
		},
		value: {
			type: Array,
			default: () => []
		}
	},
	data() {
		return {
			opened: []
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
			if (this.max === null) {
				return false;
			}

			return this.value.length >= this.max;
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
/** TODO: .k-blocks-field > :has(+ footer) { margin-bottom: var(--spacing-3);} */
.k-blocks-field > footer {
	margin-top: var(--spacing-3);
}
</style>
