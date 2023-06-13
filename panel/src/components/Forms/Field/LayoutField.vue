<template>
	<k-field v-bind="$props" class="k-layout-field">
		<template #options>
			<k-dropdown>
				<k-button icon="dots" @click="$refs.options.toggle()" />

				<k-dropdown-content ref="options" align="right">
					<k-dropdown-item icon="add" @click="$refs.layouts.select(0)">
						{{ $t("add") }}
					</k-dropdown-item>
					<hr />
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
			</k-dropdown>
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
		empty: String,
		fieldsetGroups: Object,
		fieldsets: Object,
		layouts: {
			type: Array,
			default: () => [["1/1"]]
		},
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
