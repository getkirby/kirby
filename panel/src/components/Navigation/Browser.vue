<template>
	<nav class="k-browser">
		<div class="k-browser-items">
			<label
				v-for="item in items"
				:key="item.value"
				:aria-selected="selected === item.value"
				class="k-browser-item"
			>
				<input
					:checked="selected === item.value"
					:name="name"
					:type="type"
					@change="$emit('select', item)"
				/>
				<k-item-image
					v-if="item.image"
					:image="{ ...item.image, cover: true, back: 'black' }"
					class="k-browser-item-image"
				/>
				<span class="k-browser-item-info">
					{{ item.label }}
				</span>
			</label>
		</div>
	</nav>
</template>

<script>
/**
 * @since 4.0.0
 */
export default {
	props: {
		items: {
			type: Array
		},
		name: {
			default: "items",
			type: String
		},
		selected: {
			type: String
		},
		type: {
			default: "radio",
			type: String
		}
	},
	emits: ["select"]
};
</script>

<style>
:root {
	--browser-item-hover-color-back: light-dark(
		var(--color-gray-300),
		var(--color-gray-950)
	);
	--browser-item-hover-color-text: currentColor;
	--browser-item-selected-color-back: light-dark(
		var(--color-blue-300),
		var(--color-blue-800)
	);
	--browser-item-selected-color-text: currentColor;
	--browser-item-gap: 1px;
	--browser-item-size: 1fr;
	--browser-item-height: var(--height-sm);
	--browser-item-padding: 0.25rem;
	--browser-item-rounded: var(--rounded);
}

.k-browser {
	container-type: inline-size;
	font-size: var(--text-sm);
}

.k-browser-items {
	display: grid;
	column-gap: var(--browser-item-gap);
	row-gap: var(--browser-item-gap);
	grid-template-columns: repeat(
		auto-fill,
		minmax(var(--browser-item-size), 1fr)
	);
}

.k-browser-item {
	display: flex;
	overflow: hidden;
	gap: 0.5rem;
	align-items: center;
	flex-shrink: 0;
	height: var(--browser-item-height);
	padding-inline: calc(var(--browser-item-padding) + 1px);
	border-radius: var(--browser-item-rounded);
	white-space: nowrap;
	cursor: pointer;
}
.k-browser-item:hover {
	background: var(--browser-item-hover-color-back);
	color: var(--browser-item-hover-color-text);
}

.k-browser-item-image {
	height: calc(var(--browser-item-height) - var(--browser-item-padding) * 2);
	aspect-ratio: 1/1;
	border-radius: var(--rounded-sm);
	box-shadow: var(--shadow);
	flex-shrink: 0;
}
.k-browser-item-image.k-icon-frame {
	box-shadow: none;
	background: light-dark(var(--color-white), var(--color-black));
}
.k-browser-item-image svg {
	transform: scale(0.8);
}

.k-browser-item input {
	position: absolute;
	box-shadow: var(--shadow);
	opacity: 0;
	width: 0;
}
.k-browser-item[aria-selected] {
	background: var(--browser-item-selected-color-back);
	color: var(--browser-item-selected-color-text);
}
</style>
