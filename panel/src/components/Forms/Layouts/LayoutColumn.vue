<template>
	<div
		:id="id"
		:style="{ '--width': width }"
		tabindex="0"
		class="k-column k-layout-column"
		@dblclick="$refs.blocks.choose(blocks.length)"
	>
		<k-blocks
			ref="blocks"
			v-bind="{
				endpoints,
				fieldsets,
				fieldsetGroups,
				group: 'layout',
				value: blocks
			}"
			@input="$emit('input', $event)"
			@dblclick.stop
		/>
	</div>
</template>

<script>
export const props = {
	props: {
		/**
		 * API endpoints
		 * @value { field, model, section }
		 */
		endpoints: Object,
		fieldsetGroups: Object,
		/**
		 * The fieldset definition with all fields, tabs, etc.
		 */
		fieldsets: Object,
		id: String,
		isSelected: Boolean
	}
};

/**
 * @unstable
 */
export default {
	mixins: [props],
	props: {
		blocks: Array,
		width: {
			type: String,
			default: "1/1"
		}
	},
	emits: ["input"]
};
</script>

<style>
.k-layout-column {
	position: relative;
	height: 100%;
	display: flex;
	flex-direction: column;
	min-height: 6rem;
}
.k-layout-column:focus {
	outline: 0;
}
.k-layout-column > .k-blocks {
	box-shadow: none;
	padding: 0;
	height: 100%;
	background: light-dark(var(--color-white), var(--color-gray-850));
	min-height: 4rem;
}
.k-layout-column > .k-blocks[data-empty="true"] {
	min-height: 6rem;
}

.k-layout-column > .k-blocks > .k-blocks-list {
	display: flex;
	flex-direction: column;
	height: 100%;
}
.k-layout-column
	> .k-blocks
	> .k-blocks-list
	> .k-block-container:last-of-type {
	flex-grow: 1;
}
.k-layout-column > .k-blocks > .k-blocks-list + .k-blocks-empty.k-box {
	--box-color-back: transparent;
	position: absolute;
	inset: 0;
	justify-content: center;
	opacity: 0;
	transition: opacity 0.3s;
	border: 0;
}
.k-layout-column > .k-blocks > .k-blocks-list + .k-blocks-empty:hover {
	opacity: 1;
}
</style>
