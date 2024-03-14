<template>
	<div
		v-bind="data"
		:class="['k-item', `k-item-${layout}`, $attrs.class]"
		:data-has-image="hasFigure"
		:data-layout="layout"
		:data-theme="theme"
		:style="$attrs.style"
		@click="$emit('click', $event)"
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
			<h3 class="k-item-title" :title="title">
				<k-link v-if="link !== false" :target="target" :to="link">
					<!-- eslint-disable-next-line vue/no-v-html -->
					<span v-html="text ?? '–'" />
				</k-link>
				<!-- eslint-disable-next-line vue/no-v-html -->
				<span v-else v-html="text ?? '–'" />
			</h3>
			<!-- eslint-disable-next-line vue/no-v-html -->
			<p v-if="info" class="k-item-info" v-html="info" />
		</div>

		<div
			class="k-item-options"
			:data-only-option="!buttons?.length || (!options && !$slots.options)"
		>
			<!-- Buttons -->
			<k-button
				v-for="(button, buttonIndex) in buttons"
				:key="'button-' + buttonIndex"
				v-bind="button"
			/>

			<!-- Options -->
			<slot name="options">
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
		 * @private
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
	emits: ["action", "click", "drag", "option"],
	computed: {
		hasFigure() {
			return this.image !== false && this.$helper.object.length(this.image) > 0;
		},
		title() {
			return this.$helper.string
				.stripHTML(this.$helper.string.unescapeHTML(this.text))
				.trim();
		}
	},
	methods: {
		onOption(event) {
			this.$emit("action", event);
			this.$emit("option", event);
		}
	}
};
</script>

<style>
:root {
	--item-button-height: var(--height-md);
	--item-button-width: var(--height-md);
	--item-height: auto;
	--item-height-cardlet: calc(var(--height-md) * 3);
}

.k-item {
	position: relative;
	background: var(--color-white);
	box-shadow: var(--shadow);
	border-radius: var(--rounded);
	height: var(--item-height);
	container-type: inline-size;
}
.k-item:has(a:focus) {
	outline: 2px solid var(--color-focus);
}
/** TODO: remove when firefox supports :has() */
@supports not selector(:has(*)) {
	.k-item:focus-within {
		outline: 2px solid var(--color-focus);
	}
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
/** TODO: .k-item-options:has(> :first-child:last-child) */
.k-item-options[data-only-option="true"] {
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
	height: var(--item-height);
	align-items: center;
	grid-template-columns: 1fr auto;
}
/** TODO: .k-item[data-layout="list"]:has(.k-item-image) */
.k-item[data-layout="list"][data-has-image="true"] {
	grid-template-columns: var(--item-height) 1fr auto;
}
.k-item[data-layout="list"] .k-frame {
	--ratio: 1/1;
	border-start-start-radius: var(--rounded);
	border-end-start-radius: var(--rounded);
	height: var(--item-height);
}
.k-item[data-layout="list"] .k-item-content {
	display: flex;
	min-width: 0;
	white-space: nowrap;
	gap: var(--spacing-2);
	justify-content: space-between;
}
.k-item[data-layout="list"] .k-item-title,
.k-item[data-layout="list"] .k-item-info {
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
}
.k-item[data-layout="list"] .k-item-title {
	flex-shrink: 1;
}
.k-item[data-layout="list"] .k-item-info {
	flex-shrink: 2;
}

@container (max-width: 30rem) {
	.k-item[data-layout="list"] .k-item-title {
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
	}
	.k-item[data-layout="list"] .k-item-info {
		display: none;
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
	background: hsla(0, 0%, var(--color-l-max), 50%);
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
	background: hsla(0, 0%, var(--color-l-max), 95%);
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
/** TODO: .k-item[data-layout="cardlets"]:has(.k-item-image) */
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
	height: var(--item-height);
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
</style>
