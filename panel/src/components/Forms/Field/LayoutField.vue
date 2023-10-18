<template>
	<k-field v-bind="$props" class="k-layout-field">
		<template #options>
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
				<k-dropdown-content
					ref="options"
					:options="[
						{
							click: () => $refs.layouts.copy(),
							disabled: isEmpty,
							icon: 'template',
							text: $t('copy.all')
						},
						{
							click: () => $refs.layouts.pasteboard(),
							icon: 'download',
							text: $t('paste')
						},
						'-',
						{
							click: () => $refs.layouts.removeAll(),
							disabled: isEmpty,
							icon: 'trash',
							text: $t('delete.all')
						}
					]"
					align-x="end"
				/>
			</k-button-group>
		</template>
		<k-layouts ref="layouts" v-bind="$props" @input="$emit('input', $event)" />
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
		fieldsetGroups: Object,
		fieldsets: Object,
		layouts: {
			type: Array,
			default: () => [["1/1"]]
		},
		selector: Object,
		settings: Object,
		value: {
			type: Array,
			default: () => []
		}
	},
	computed: {
		isEmpty() {
			return this.value.length === 0;
		}
	}
};
</script>
