<template>
	<nav class="k-toolbar" :data-theme="theme">
		<template v-for="(button, index) in buttons">
			<div v-if="button === '|'" :key="index" class="k-toolbar-divider" />

			<k-button
				v-else
				:key="index"
				:current="button.current"
				:disabled="button.disabled"
				:icon="button.icon"
				:title="button.label"
				tabindex="-1"
				class="k-toolbar-button"
				@mousedown.native.prevent="
					button.dropdown?.length
						? $refs[index + '-dropdown'][0].toggle()
						: button.click?.($event)
				"
			/>

			<k-dropdown-content
				v-if="button.dropdown?.length"
				:key="index + '-dropdown'"
				:ref="index + '-dropdown'"
				:theme="theme === 'dark' ? 'light' : 'dark'"
			>
				<template v-for="(item, itemIndex) in button.dropdown">
					<hr v-if="item === '-'" :key="itemIndex" class="k-toolbar-divider" />

					<k-dropdown-item
						v-else
						:key="itemIndex"
						:current="item.current"
						:disabled="item.disabled"
						:icon="item.icon"
						@mousedown.native.prevent="item.click?.($event)"
					>
						{{ item.label }}
					</k-dropdown-item>
				</template>
			</k-dropdown-content>
		</template>
	</nav>
</template>

<script>
export const props = {
	props: {
		/**
		 * Buttons to show in the toolbar
		 */
		buttons: {
			type: Array,
			default: () => []
		},
		/**
		 * @values "light", "dark"
		 */
		theme: {
			type: String,
			default: "light"
		}
	}
};

export default {
	mixins: [props],
	methods: {
		/**
		 * Closes all dropdowns etc.
		 */
		close() {
			for (const ref in this.$refs) {
				const component = this.$refs[ref][0];

				if (typeof component?.close === "function") {
					component.close();
				}
			}
		}
	}
};
</script>

<style>
:root {
	--toolbar-size: var(--height);
	--toolbar-text: var(--color-black);
	--toolbar-back: var(--color-white);
	--toolbar-hover: rgba(239, 239, 239, 0.5);
	--toolbar-border: rgba(0, 0, 0, 0.1);
	--toolbar-current: var(--color-focus);
}

.k-toolbar {
	display: flex;
	max-width: 100%;
	height: var(--toolbar-size);
	align-items: center;
	overflow-x: auto;
	overflow-y: hidden;
	color: var(--toolbar-text);
	background: var(--toolbar-back);
	border-radius: var(--rounded);
}

.k-toolbar[data-theme="dark"] {
	--toolbar-text: var(--color-white);
	--toolbar-back: var(--color-black);
	--toolbar-hover: rgba(255, 255, 255, 0.2);
	--toolbar-border: var(--color-gray-800);
}

.k-toolbar > .k-toolbar-divider {
	height: var(--toolbar-size);
	width: 1px;
	border-left: 1px solid var(--toolbar-border);
}

.k-toolbar-button.k-button {
	--button-width: var(--toolbar-size);
	--button-height: var(--toolbar-size);
}
.k-toolbar-button:hover {
	--button-color-back: var(--toolbar-hover);
}
.k-toolbar .k-button[aria-current] {
	--button-color-text: var(--toolbar-current);
}

:where(.k-textarea-input, .k-writer-input):not(:focus-within) {
	--toolbar-text: var(--color-gray-400);
	--toolbar-border: var(--color-background);
}
/** TODO: .k-toolbar:not([data-inline="true"]):has(~ :focus-within) */
:where(.k-textarea-input, .k-writer-input):focus-within
	.k-toolbar:not([data-inline="true"]) {
	position: sticky;
	top: var(--header-sticky-offset);
	inset-inline: 0;
	z-index: 1;
	box-shadow: rgba(0, 0, 0, 0.05) 0 2px 5px;
}
</style>
