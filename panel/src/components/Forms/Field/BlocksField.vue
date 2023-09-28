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
				<k-dropdown-content ref="options" align-x="end">
					<k-dropdown-item
						:disabled="isEmpty"
						icon="template"
						@click="$refs.blocks.copyAll()"
					>
						{{ $t("copy.all") }}
					</k-dropdown-item>
					<k-dropdown-item
						:disabled="isFull"
						icon="download"
						@click="$refs.blocks.pasteboard()"
					>
						{{ $t("paste") }}
					</k-dropdown-item>
					<hr />
					<k-dropdown-item
						:disabled="isEmpty"
						icon="trash"
						@click="$refs.blocks.removeAll()"
					>
						{{ $t("delete.all") }}
					</k-dropdown-item>
				</k-dropdown-content>
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
