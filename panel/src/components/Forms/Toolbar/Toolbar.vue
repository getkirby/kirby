<template>
	<nav class="k-toolbar" :data-theme="theme">
		<template v-for="(button, index) in buttons">
			<hr v-if="button === '|'" :key="index" />

			<k-button
				v-else-if="button.when ?? true"
				:key="index"
				:current="button.current"
				:disabled="button.disabled"
				:icon="button.icon"
				:title="button.label ?? button.title"
				:class="['k-toolbar-button', button.class]"
				tabindex="0"
				@keydown.native="button.key?.($event)"
				@click="
					button.dropdown?.length
						? $refs[index + '-dropdown'][0].toggle()
						: button.click?.($event)
				"
			/>
			<k-dropdown-content
				v-if="(button.when ?? true) && button.dropdown?.length"
				:key="index + '-dropdown'"
				:ref="index + '-dropdown'"
				:options="button.dropdown"
				:theme="theme === 'dark' ? 'light' : 'dark'"
			/>
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

/**
 * Toolbar to display various buttons with/without dropdowns
 * and trigger related actions
 * @since 4.0.0
 *
 * @example <k-toolbar :buttons="[
 * 	{ icon: 'heart', click: () => alert('I love you') }
 * ]" />
 */
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

.k-toolbar > hr {
	height: var(--toolbar-size);
	width: 1px;
	border-left: 1px solid var(--toolbar-border);
}

.k-toolbar-button.k-button {
	--button-width: var(--toolbar-size);
	--button-height: var(--toolbar-size);
	--button-rounded: 0;
	outline-offset: -2px;
}
.k-toolbar-button:hover {
	--button-color-back: var(--toolbar-hover);
}
.k-toolbar .k-button[aria-current] {
	--button-color-text: var(--toolbar-current);
}
.k-toolbar > .k-button:first-child {
	border-start-start-radius: var(--rounded);
	border-end-start-radius: var(--rounded);
}
.k-toolbar > .k-button:last-child {
	border-start-end-radius: var(--rounded);
	border-end-end-radius: var(--rounded);
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
