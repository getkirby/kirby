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
				<k-dropdown-content ref="options" align-x="end">
					<k-dropdown-item
						:disabled="isEmpty"
						icon="template"
						@click="$refs.layouts.copy()"
					>
						{{ $t("copy.all") }}
					</k-dropdown-item>
					<k-dropdown-item icon="download" @click="$refs.layouts.pasteboard()">
						{{ $t("paste") }}
					</k-dropdown-item>
					<hr />
					<k-dropdown-item
						:disabled="isEmpty"
						icon="trash"
						@click="$refs.layouts.removeAll()"
					>
						{{ $t("delete.all") }}
					</k-dropdown-item>
				</k-dropdown-content>
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
			return this.value?.length === 0;
		}
	}
};
</script>
