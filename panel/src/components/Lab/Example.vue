<template>
	<div class="k-ui-example" :data-flex="flex" tabindex="0">
		<header class="k-ui-example-header">
			<h3 class="k-ui-example-label">{{ label }}</h3>
			<k-button-group
				v-if="code"
				layout="collapsed"
				class="k-ui-example-inspector"
			>
				<k-button
					icon="preview"
					:theme="mode === 'preview' ? 'info' : null"
					size="xs"
					@click="mode = 'preview'"
				/>
				<k-button
					icon="search"
					:theme="mode === 'inspect' ? 'info' : null"
					size="xs"
					@click="mode = 'inspect'"
				/>
				<k-button
					icon="code"
					:theme="mode === 'raw' ? 'info' : null"
					size="xs"
					@click="mode = 'raw'"
				/>
			</k-button-group>
		</header>

		<!-- Preview -->
		<div v-show="mode === 'preview'" ref="preview" class="k-ui-example-canvas">
			<slot />
		</div>
		<!-- Inspect -->
		<div v-show="mode === 'inspect'" class="k-ui-example-code">
			<k-ui-code language="html">{{ component }}</k-ui-code>
		</div>
		<!-- Raw -->
		<div v-if="mode === 'raw'" class="k-ui-example-code">
			<k-ui-code language="html">{{ $refs.preview?.innerHTML }}</k-ui-code>
		</div>
	</div>
</template>

<script>
export default {
	props: {
		code: {
			type: Boolean,
			default: true
		},
		label: String,
		flex: Boolean
	},
	data() {
		return {
			mode: "preview"
		};
	},
	computed: {
		component() {
			return window.UiExamples[this.label];
		}
	}
};
</script>

<style>
.k-ui-example {
	position: relative;
	container-type: inline-size;
	max-width: 100%;
	outline-offset: -2px;
	border-radius: var(--rounded);
	border: 1px solid var(--color-gray-300);
}
.k-ui-example + .k-ui-example {
	margin-top: var(--spacing-12);
}

.k-ui-example-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	height: var(--height-md);
	padding-block: var(--spacing-3);
	padding-inline: var(--spacing-2);
	border-bottom: 1px solid var(--color-gray-300);
}
.k-ui-example-label {
	font-size: 12px;
	color: var(--color-text-dimmed);
}

.k-ui-example-canvas,
.k-ui-example-code {
	padding: var(--spacing-16);
}
.k-ui-example[data-flex] .k-ui-example-canvas {
	display: flex;
	align-items: center;
	gap: var(--spacing-6);
}

.k-ui-example-inspector {
	--icon-size: 13px;
	--button-color-icon: var(--color-gray-500);
}
.k-ui-example-inspector .k-button:not([data-theme]):hover {
	--button-color-icon: var(--color-gray-600);
}
.k-ui-example-inspector .k-button:where([data-theme]) {
	--button-color-icon: var(--color-gray-800);
}
</style>
