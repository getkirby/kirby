<template>
	<k-table
		v-bind="{ ...options, ...$props }"
		:rows="items"
		:class="['k-table-collection-layout', $attrs.class]"
		:style="$attrs.style"
		@change="$emit('change', $event)"
		@option="$emit('option', $event)"
		@select="$emit('select', $event)"
		@sort="$emit('sort', $event)"
	>
		<template v-if="$slots.options" #options="{ row: item, rowIndex: index }">
			<slot name="options" v-bind="{ item, index }" />
		</template>
	</k-table>
</template>

<script>
import CollectionLayout from "./CollectionLayout.vue";

export default {
	extends: CollectionLayout,
	emits: ["change", "option", "select", "sort"]
};
</script>
