<template>
	<div
		v-bind="data"
		:class="['k-item', `k-${layout}-item`, $attrs.class]"
		:data-has-image="hasFigure"
		:data-layout="layout"
		:data-selecting="selecting"
		:data-selectable="selectable"
		:data-theme="theme"
		:style="$attrs.style"
		@click="onClick"
		@dragstart="$emit('drag', $event)"
	>
		<!-- Image -->
		<slot name="image">
			<k-item-image
				v-if="hasFigure"
				:image="image"
				:layout="layout"
				:width="width"
			/>
		</slot>

		<!-- Sort handle -->
		<k-sort-handle v-if="sortable" class="k-item-sort-handle" tabindex="-1" />

		<!-- Content -->
		<div class="k-item-content">
			<h3 class="k-item-title" :title="title(text)">
				<k-link
					v-if="link !== false && selecting !== true"
					:target="target"
					:to="link"
				>
					<!-- eslint-disable-next-line vue/no-v-html -->
					<span v-html="text ?? '–'" />
				</k-link>
				<!-- eslint-disable-next-line vue/no-v-html -->
				<span v-else v-html="text ?? '–'" />
			</h3>
			<!-- eslint-disable-next-line vue/no-v-html -->
			<p v-if="info" :title="title(info)" class="k-item-info" v-html="info" />
		</div>

		<div
			v-if="buttons?.length || options || $slots.options || selecting"
			class="k-item-options"
		>
			<!-- Buttons -->
			<k-button
				v-for="button in buttons"
				:key="JSON.stringify(button)"
				v-bind="button"
			/>

			<label v-if="selecting" class="k-item-options-checkbox" @click.stop>
				<input
					ref="selector"
					type="checkbox"
					:disabled="!selectable"
					@change="$emit('select', $event)"
				/>
			</label>

			<!-- Options -->
			<slot v-else name="options">
				<k-options-dropdown
					v-if="options"
					:options="options"
					class="k-item-options-dropdown"
					@option="onOption"
				/>
			</slot>
		</div>
	</div>
</template>

<script>
import { props as ItemImageProps } from "./ItemImage.vue";
import { layout } from "@/mixins/props.js";
/**
 * A collection item that can be displayed in various layouts
 */
export default {
	mixins: [ItemImageProps, layout],
	inheritAttrs: false,
	props: {
		/**
		 * Additional inline buttons in the item's footer
		 */
		buttons: {
			type: Array,
			default: () => []
		},
		/**
		 * @internal
		 */
		data: Object,
		/**
		 * The optional info text that will be show next or below the main text
		 */
		info: String,
		/**
		 * An optional link
		 */
		link: {
			type: [Boolean, String, Function]
		},
		/**
		 * Array of dropdown options
		 */
		options: {
			type: [Array, Function, String]
		},
		/**
		 * If `true`, the item will be selectable via a checkbox
		 * @since 5.0.0
		 */
		selecting: Boolean,
		/**
		 * If `false`, the select checkbox will be disabled
		 * @since 5.0.0
		 */
		selectable: Boolean,
		/**
		 * If `true`, the sort handle will be shown on hover
		 */
		sortable: Boolean,
		/**
		 * Sets a target attribute if a link is also set
		 */
		target: String,
		/**
		 * The main text for the item
		 */
		text: String,
		/**
		 * Visual theme for items
		 * @values "disabled"
		 */
		theme: String
	},
	emits: ["action", "click", "drag", "option", "select"],
	computed: {
		hasFigure() {
			return this.image !== false && this.$helper.object.length(this.image) > 0;
		}
	},
	methods: {
		onClick(event) {
			if (this.selecting && this.selectable) {
				return this.$refs.selector.click();
			}

			this.$emit("click", event);
		},
		onOption(event) {
			this.$emit("action", event);
			this.$emit("option", event);
		},
		title(text) {
			return this.$helper.string
				.stripHTML(this.$helper.string.unescapeHTML(text))
				.trim();
		}
	}
};
</script>

<style>
:root {
	--item-button-height: var(--height-md);
	--item-button-width: var(--height-md);
	--item-color-back: light-dark(var(--color-white), var(--color-gray-850));
	--item-height: auto;
	--item-height-cardlet: calc(var(--height-md) * 3);
	--item-shadow: var(--shadow-sm);
}

.k-item {
	position: relative;
	background: var(--item-color-back);
	box-shadow: var(--item-shadow);
	border-radius: var(--rounded);
	min-height: var(--item-height);
	container-type: inline-size;
}
.k-item:has(a:focus) {
	outline: 2px solid var(--color-focus);
}

.k-item .k-icon-frame {
	--back: var(--color-gray-300);
}

.k-item-content {
	line-height: 1.25;
	overflow: hidden;
	padding: var(--spacing-2);
}
.k-item-content a:focus {
	outline: 0;
}
.k-item-content a::after {
	content: "";
	position: absolute;
	inset: 0;
}
.k-item-info {
	color: var(--color-text-dimmed);
}
.k-item-options {
	transform: translate(0);
	z-index: 1;
	display: flex;
	align-items: center;
	justify-content: space-between;
}
.k-item-options:has(> :first-child:last-child) {
	justify-content: flex-end;
}
.k-item-options .k-button {
	--button-height: var(--item-button-height);
	--button-width: var(--item-button-width);
}

.k-item .k-sort-button {
	position: absolute;
	z-index: 2;
}
.k-item:not(:hover):not(.k-sortable-fallback) .k-sort-button {
	opacity: 0;
}

/** List */
.k-item[data-layout="list"] {
	--item-height: var(
		--field-input-height
	); /* TODO: change back to --height-md after input refactoring */
	--item-button-height: var(--item-height);
	--item-button-width: auto;

	display: grid;
	align-items: center;
	grid-template-columns: 1fr auto;
}
.k-item[data-layout="list"][data-has-image="true"] {
	grid-template-columns: var(--item-height) 1fr auto;
}
.k-item[data-layout="list"] .k-frame {
	--ratio: 1/1;
	border-start-start-radius: var(--rounded);
	border-end-start-radius: var(--rounded);
	height: 100%;
}
.k-item[data-layout="list"] .k-item-content {
	display: flex;
	min-width: 0;
	flex-wrap: wrap;
	column-gap: var(--spacing-4);
	justify-content: space-between;
}
.k-item[data-layout="list"] .k-item-title,
.k-item[data-layout="list"] .k-item-info {
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
}
/** Provides a consistent look when texts are long in small dialogs */
@container (max-width: 25rem) {
	.k-item[data-layout="list"] .k-item-content:has(.k-item-info) {
		flex-direction: column;
	}
}
.k-item[data-layout="list"] .k-sort-button {
	--button-width: calc(1.5rem + var(--spacing-1));
	--button-height: var(--item-height);
	left: calc(-1 * var(--button-width));
}

/** Cardlet & cards */
.k-item:is([data-layout="cardlets"], [data-layout="cards"]) .k-sort-button {
	top: var(--spacing-2);
	inset-inline-start: var(--spacing-2);
	color: light-dark(var(--color-black), var(--color-white));
	background: hsla(0, 0%, light-dark(100%, 7%), 50%);
	backdrop-filter: blur(5px);
	box-shadow: 0 2px 5px hsla(0, 0%, 0%, 20%);
	--button-width: 1.5rem;
	--button-height: 1.5rem;
	--button-rounded: var(--rounded-sm);
	--button-padding: 0;
	--icon-size: 14px;
}

.k-item:is([data-layout="cardlets"], [data-layout="cards"])
	.k-sort-button:hover {
	background: hsla(0, 0%, light-dark(100%, 7%), 95%);
}

/** Cardlet */
.k-item[data-layout="cardlets"] {
	--item-height: var(--item-height-cardlet);
	display: grid;
	grid-template-areas:
		"content"
		"options";
	grid-template-columns: 1fr;
	grid-template-rows: 1fr var(--height-md);
}
.k-item[data-layout="cardlets"][data-has-image="true"] {
	grid-template-areas:
		"image content"
		"image options";
	grid-template-columns: minmax(0, var(--item-height)) 1fr;
}
.k-item[data-layout="cardlets"] .k-frame {
	grid-area: image;
	border-start-start-radius: var(--rounded);
	border-end-start-radius: var(--rounded);
	aspect-ratio: auto;
}
.k-item[data-layout="cardlets"] .k-item-content {
	grid-area: content;
}
.k-item[data-layout="cardlets"] .k-item-info {
	margin-top: 0.125em;
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
}
.k-item[data-layout="cardlets"] .k-item-options {
	grid-area: options;
}

/** Card */
.k-item[data-layout="cards"] {
	display: flex;
	flex-direction: column;
	/* container-type: inline-size; */
}
.k-item[data-layout="cards"] .k-frame {
	border-start-start-radius: var(--rounded);
	border-start-end-radius: var(--rounded);
}
.k-item[data-layout="cards"] .k-item-content {
	flex-grow: 1;
	padding: var(--spacing-2);
}
.k-item[data-layout="cards"] .k-item-info {
	margin-top: 0.125em;
}

/** Theme: disabled */
.k-item[data-theme="disabled"] {
	background: transparent;
	box-shadow: none;
	outline: 1px solid var(--color-border);
	outline-offset: -1px;
}

/** Selectable state */
.k-item[data-selecting="true"][data-selectable="true"] {
	cursor: pointer;
}
.k-item-options-checkbox {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	height: var(--item-button-height);
	width: var(--item-button-height);
	flex-shrink: 0;
}
.k-item[data-selectable="true"]:has(.k-item-options-checkbox input:checked) {
	--item-color-back: light-dark(var(--color-blue-250), var(--color-blue-800));
	--item-shadow: 0 1px 3px 0 rgba(0 0 0 / 0.25), 0 1px 2px 0 rgba(0 0 0 / 0.05);
}
</style>
