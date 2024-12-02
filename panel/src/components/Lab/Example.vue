<template>
	<div class="k-lab-example" :data-flex="flex" tabindex="0">
		<header class="k-lab-example-header">
			<h3 class="k-lab-example-label">{{ label }}</h3>
			<k-button-group
				v-if="code"
				layout="collapsed"
				class="k-lab-example-inspector"
			>
				<k-button
					:theme="mode === 'preview' ? 'info' : null"
					icon="preview"
					size="xs"
					title="Preview"
					@click="mode = 'preview'"
				/>
				<k-button
					:theme="mode === 'inspect' ? 'info' : null"
					icon="code"
					size="xs"
					title="Vue code"
					@click="mode = 'inspect'"
				/>
			</k-button-group>
		</header>

		<!-- Preview -->
		<div v-if="mode === 'preview'" class="k-lab-example-canvas">
			<slot />
		</div>
		<!-- Inspect -->
		<div v-if="mode === 'inspect'" class="k-lab-example-code">
			<k-code language="html">{{ component }}</k-code>
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
.k-lab-example {
	position: relative;
	container-type: inline-size;
	max-width: 100%;
	outline-offset: -2px;
	border-radius: var(--rounded);
	border: 1px solid var(--color-border);
}
.k-lab-example + .k-lab-example {
	margin-top: var(--spacing-12);
}

.k-lab-example-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	height: var(--height-md);
	padding-block: var(--spacing-3);
	padding-inline: var(--spacing-2);
	border-bottom: 1px solid var(--color-border);
}
.k-lab-example-label {
	font-size: 12px;
	color: var(--color-text-dimmed);
}

.k-lab-example-canvas,
.k-lab-example-code {
	padding: var(--spacing-16);
}
.k-lab-example[data-flex="true"] .k-lab-example-canvas {
	display: flex;
	align-items: center;
	gap: var(--spacing-6);
}

.k-lab-example-inspector {
	--icon-size: 13px;
	--button-color-icon: var(--color-gray-500);
}
.k-lab-example-inspector .k-button:not([data-theme]):hover {
	--button-color-icon: var(--color-gray-600);
}
.k-lab-example-inspector .k-button:where([data-theme]) {
	--button-color-icon: var(--color-gray-800);
}
</style>
