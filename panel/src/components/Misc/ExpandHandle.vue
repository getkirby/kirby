<template>
	<k-button
		:icon="icon"
		size="xs"
		:class="'k-expand-handle--' + attach"
		class="k-expand-handle"
		@click="$emit('update', !isExpanded)"
	/>
</template>

<script>
export default {
	props: {
		attach: {
			type: String,
			default: "start"
		},
		isExpanded: {
			type: Boolean,
			default: false
		}
	},
	emits: ["update"],
	computed: {
		icon() {
			if (this.attach === "start") {
				return this.isExpanded ? "angle-left" : "angle-right";
			} else {
				return this.isExpanded ? "angle-right" : "angle-left";
			}
		}
	}
};
</script>

<style>
:root {
	--expand-handle-back: var(--color-white);
}

.k-button.k-expand-handle {
	--button-width: calc(var(--drawer-header-height) / 2);
	--button-height: var(--drawer-header-height);
	position: absolute;
	inset-block: 0;
	border-radius: 0;
	overflow: visible;
	opacity: 0;
	transition: opacity 0.2s;
}
.k-button.k-expand-handle--start {
	--button-align: flex-start;
	inset-inline-start: 100%;
	align-items: flex-start;
}
.k-button.k-expand-handle--end {
	--button-align: flex-end;
	inset-inline-end: 100%;
	align-items: flex-end;
}

.k-expand-handle .k-button-icon {
	display: grid;
	place-items: center;
	width: 100%;
	height: 100%;
	background: var(--expand-handle-back);
}
.k-button.k-expand-handle--start .k-button-icon {
	border-start-end-radius: var(--button-rounded);
	border-end-end-radius: var(--button-rounded);
}
.k-button.k-expand-handle--end .k-button-icon {
	border-start-start-radius: var(--button-rounded);
	border-end-start-radius: var(--button-rounded);
}

.k-expand-handle:focus {
	outline: 0;
}
.k-expand-handle:focus-visible .k-button-icon {
	outline: var(--outline);
	/* With a radius on all ends, the outline looks nicer */
	border-radius: var(--button-rounded);
}
.k-expand-handle:focus-visible {
	opacity: 1;
}
</style>
