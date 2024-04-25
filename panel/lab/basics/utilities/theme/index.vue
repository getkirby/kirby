<template>
	<k-lab-examples>
		<k-lab-example label="data-theme">
			<k-text>
				<p>
					The <code>data-theme</code> attribute can be slapped on any element to
					apply pre-defined theme colors via CSS. The element receives the
					following CSS variables that you can then use for your styles:
				</p>
				<k-code language="css">{{ variables }}</k-code>
			</k-text>
		</k-lab-example>

		<k-lab-example label="data-theme=blue|info">
			<div class="k-theming-example" data-theme="blue">
				<k-icon type="star" />
				<p class="k-theming-example-text">Example Text</p>
				<p class="k-theming-example-text-dimmed">Example Text Dimmed</p>
				<p class="k-theming-example-back"></p>
			</div>
		</k-lab-example>
		<k-lab-example label="data-theme=green|positive">
			<div class="k-theming-example" data-theme="green">
				<k-icon type="star" />
				<p class="k-theming-example-text">Example Text</p>
				<p class="k-theming-example-text-dimmed">Example Text Dimmed</p>
				<p class="k-theming-example-back"></p>
			</div>
		</k-lab-example>
		<k-lab-example label="data-theme=yellow|warning">
			<div class="k-theming-example" data-theme="yellow">
				<k-icon type="star" />
				<p class="k-theming-example-text">Example Text</p>
				<p class="k-theming-example-text-dimmed">Example Text Dimmed</p>
				<p class="k-theming-example-back"></p>
			</div>
		</k-lab-example>
		<k-lab-example label="data-theme=orange|notice">
			<div class="k-theming-example" data-theme="orange">
				<k-icon type="star" />
				<p class="k-theming-example-text">Example Text</p>
				<p class="k-theming-example-text-dimmed">Example Text Dimmed</p>
				<p class="k-theming-example-back"></p>
			</div>
		</k-lab-example>
		<k-lab-example label="data-theme=red|error|negative">
			<div class="k-theming-example" data-theme="red">
				<k-icon type="star" />
				<p class="k-theming-example-text">Example Text</p>
				<p class="k-theming-example-text-dimmed">Example Text Dimmed</p>
				<p class="k-theming-example-back"></p>
			</div>
		</k-lab-example>
		<k-lab-example label="data-theme=pink|love">
			<div class="k-theming-example" data-theme="love">
				<k-icon type="star" />
				<p class="k-theming-example-text">Example Text</p>
				<p class="k-theming-example-text-dimmed">Example Text Dimmed</p>
				<p class="k-theming-example-back"></p>
			</div>
		</k-lab-example>
		<k-lab-example label="data-theme=aqua">
			<div class="k-theming-example" data-theme="aqua">
				<k-icon type="star" />
				<p class="k-theming-example-text">Example Text</p>
				<p class="k-theming-example-text-dimmed">Example Text Dimmed</p>
				<p class="k-theming-example-back"></p>
			</div>
		</k-lab-example>
		<k-lab-example label="data-theme=purple">
			<div class="k-theming-example" data-theme="purple">
				<k-icon type="star" />
				<p class="k-theming-example-text">Example Text</p>
				<p class="k-theming-example-text-dimmed">Example Text Dimmed</p>
				<p class="k-theming-example-back"></p>
			</div>
		</k-lab-example>
		<k-lab-example label="data-theme=gray|passive">
			<div class="k-theming-example" data-theme="passive">
				<k-icon type="star" />
				<p class="k-theming-example-text">Example Text</p>
				<p class="k-theming-example-text-dimmed">Example Text Dimmed</p>
				<p class="k-theming-example-back"></p>
			</div>
		</k-lab-example>
		<k-lab-example label="data-theme=white">
			<div class="k-theming-example" data-theme="white">
				<k-icon type="star" />
				<p class="k-theming-example-text">Example Text</p>
				<p class="k-theming-example-text-dimmed">Example Text Dimmed</p>
				<p class="k-theming-example-back"></p>
			</div>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		variables() {
			return `
	--theme-color-100
	--theme-color-200
	--theme-color-300
	--theme-color-400
	--theme-color-500
	--theme-color-600
	--theme-color-700
	--theme-color-800
	--theme-color-900

	--theme-color-text
	--theme-color-text-dimmed
	--theme-color-back
	--theme-color-hover
	--theme-color-icon
			`;
		}
	}
};
</script>

<style>
.k-theming-example {
	display: flex;
	align-items: center;
	gap: var(--spacing-3);
}
.k-theming-example svg {
	color: var(--theme-color-icon);
}
.k-theming-example-text {
	color: var(--theme-color-text);
}
.k-theming-example-text-dimmed {
	color: var(--theme-color-text-dimmed);
}
.k-theming-example-back {
	width: 2rem;
	height: 2rem;
	border-radius: var(--rounded);
	background: var(--theme-color-back);
}
</style>
