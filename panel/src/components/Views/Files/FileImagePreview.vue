<template>
	<k-file-preview
		:details="details"
		:options="options"
		:data-has-focus="Boolean(focus)"
	>
		<template #thumb>
			<k-coords-input
				:disabled="!focusable"
				:value="focus"
				@input="setFocus($event)"
			>
				<img v-bind="image" @dragstart.prevent />
			</k-coords-input>
		</template>

		<!-- <div v-if="image.src" class="k-file-preview-focus-info">
			<dt>{{ $t("file.focus.title") }}</dt>
			<dd>
				<k-file-focus-button
					v-if="focusable"
					ref="focus"
					:focus="focus"
					@set="setFocus"
				/>
				<template v-else-if="focus">
					{{ focus.x }}% {{ focus.y }}%
				</template>
				<template v-else>–</template>
			</dd>
		</div> -->
	</k-file-preview>
</template>

<script>
export default {
	props: {
		details: {
			default: () => [],
			type: Array
		},
		focusable: Boolean,
		image: {
			default: () => ({}),
			type: Object
		},
		url: String
	},
	emits: ["focus"],
	computed: {
		focus() {
			const focus = this.$store.getters["content/values"]()["focus"];

			if (!focus) {
				return;
			}

			const [x, y] = focus.replaceAll("%", "").split(" ");

			return { x: parseFloat(x), y: parseFloat(y) };
		},
		options() {
			return [
				{
					icon: "open",
					text: this.$t("open"),
					link: this.url,
					target: "_blank"
				},
				{
					icon: "cancel",
					text: this.$t("file.focus.reset"),
					click: () => this.$refs.focus.reset(),
					when: this.focusable && this.focus
				},
				{
					icon: "preview",
					text: this.$t("file.focus.placeholder"),
					click: () => this.$refs.focus.set(),
					when: this.focusable && !this.focus
				}
			];
		}
	},
	methods: {
		setFocus(focus) {
			if (!focus) {
				focus = null;
			} else if (this.$helper.object.isObject(focus) === true) {
				focus = `${focus.x.toFixed(1)}% ${focus.y.toFixed(1)}%`;
			}

			this.$store.dispatch("content/update", ["focus", focus]);
		}
	}
};
</script>

<style>
.k-file-preview .k-coords-input {
	--opacity-disabled: 1;
	--range-thumb-color: hsl(216 60% 60% / 0.75);
	--range-thumb-size: 1.25rem;
	--range-thumb-shadow: none;
	cursor: crosshair;
}
.k-file-preview .k-coords-input-thumb::after {
	--size: 0.4rem;
	--pos: calc(50% - (var(--size) / 2));

	position: absolute;
	top: var(--pos);
	inset-inline-start: var(--pos);
	width: var(--size);
	height: var(--size);
	content: "";
	background: white;
	border-radius: 50%;
}
.k-file-preview:not([data-has-focus="true"]) .k-coords-input-thumb {
	display: none;
}

.k-file-preview-focus-info dd {
	display: flex;
	align-items: center;
}
.k-file-preview-focus-info .k-button {
	--button-color-back: var(--color-gray-800);
	--button-padding: var(--spacing-2);
	--button-color-back: var(--color-gray-800);
}
.k-file-preview[data-has-focus="true"] .k-file-preview-focus-info .k-button {
	flex-direction: row-reverse;
}
</style>
