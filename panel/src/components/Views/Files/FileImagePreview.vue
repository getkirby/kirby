<template>
	<k-file-preview
		:details="details"
		:options="options"
		:data-has-focus="Boolean(focus)"
		class="k-file-image-preview"
	>
		<k-coords-input
			:disabled="!focusable"
			:value="focus"
			@input="setFocus($event)"
		>
			<img v-bind="image" @dragstart.prevent />
		</k-coords-input>

		<template #details>
			<div v-if="image.src" class="k-file-image-preview-focus">
				<dt>{{ $t("file.focus.title") }}</dt>
				<dd>
					<k-button
						v-if="focusable"
						ref="focus"
						:icon="focus ? 'cancel-small' : 'preview'"
						:title="focus ? $t('file.focus.reset') : undefined"
						size="xs"
						variant="filled"
						@click="focus ? setFocus(undefined) : setFocus({ x: 50, y: 50 })"
					>
						<template v-if="focus">{{ focus.x }}% {{ focus.y }}%</template>
						<template v-else>{{ $t("file.focus.placeholder") }}</template>
					</k-button>
					<template v-else-if="focus"> {{ focus.x }}% {{ focus.y }}% </template>
					<template v-else>–</template>
				</dd>
			</div>
		</template>
	</k-file-preview>
</template>

<script>
/**
 * @since 4.3.0
 */
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
.k-file-image-preview .k-coords-input {
	--opacity-disabled: 1;
	--range-thumb-color: hsl(216 60% 60% / 0.75);
	--range-thumb-size: 1.25rem;
	--range-thumb-shadow: none;
	cursor: crosshair;
}
.k-file-image-preview .k-coords-input-thumb::after {
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
.k-file-image-preview:not([data-has-focus="true"]) .k-coords-input-thumb {
	display: none;
}

.k-file-image-preview-focus dd {
	display: flex;
	align-items: center;
}
.k-file-image-preview-focus .k-button {
	--button-color-back: var(--color-gray-800);
	--button-padding: var(--spacing-2);
	--button-color-back: var(--color-gray-800);
}
</style>
