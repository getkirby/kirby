<template>
	<div
		:data-has-focus="hasFocus"
		class="k-default-file-preview k-image-file-preview"
	>
		<k-file-preview-frame :options="options">
			<k-coords-input
				:disabled="!isFocusable"
				:value="focus"
				@input="setFocus($event)"
			>
				<img v-bind="image" @dragstart.prevent />
			</k-coords-input>
		</k-file-preview-frame>

		<k-file-preview-details :details="details">
			<div v-if="image.src" class="k-image-file-preview-focus">
				<dt>{{ $t("file.focus.title") }}</dt>
				<dd>
					<k-button
						v-if="isFocusable"
						ref="focus"
						:icon="focus ? 'cancel-small' : 'preview'"
						:title="focus ? $t('file.focus.reset') : undefined"
						size="xs"
						variant="filled"
						@click="focus ? setFocus(undefined) : setFocus({ x: 50, y: 50 })"
					>
						<template v-if="hasFocus">{{ focus.x }}% {{ focus.y }}%</template>
						<template v-else>{{ $t("file.focus.placeholder") }}</template>
					</k-button>
					<template v-else-if="hasFocus">
						{{ focus.x }}% {{ focus.y }}%
					</template>
					<template v-else>–</template>
				</dd>
			</div>
		</k-file-preview-details>
	</div>
</template>

<script>
/**
 * File view preview for image files
 * @since 5.0.0
 */
export default {
	props: {
		content: {
			default: () => ({}),
			type: Object
		},
		details: Array,
		focusable: Boolean,
		image: {
			default: () => ({}),
			type: Object
		},
		isLocked: Boolean,
		url: String
	},
	emits: ["focus", "input"],
	computed: {
		focus() {
			const focus = this.content.focus;

			if (!focus) {
				return;
			}

			const [x, y] = focus.replaceAll("%", "").split(" ");

			return { x: parseFloat(x), y: parseFloat(y) };
		},
		hasFocus() {
			return Boolean(this.focus);
		},
		isFocusable() {
			return this.focusable === true && this.isLocked !== true;
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
					click: () => this.setFocus(undefined),
					when: this.isFocusable && this.hasFocus
				},
				{
					icon: "preview",
					text: this.$t("file.focus.placeholder"),
					click: () => this.setFocus({ x: 50, y: 50 }),
					when: this.isFocusable && !this.hasFocus
				}
			];
		}
	},
	methods: {
		setFocus(focus) {
			if (this.isFocusable === false) {
				return false;
			}

			if (!focus) {
				focus = null;
			} else if (this.$helper.object.isObject(focus) === true) {
				focus = `${focus.x.toFixed(1)}% ${focus.y.toFixed(1)}%`;
			}

			this.$emit("input", { focus });
		}
	}
};
</script>

<style>
.k-image-file-preview .k-coords-input {
	--opacity-disabled: 1;
	--range-thumb-color: hsl(216 60% 60% / 0.75);
	--range-thumb-size: 1.25rem;
	--range-thumb-shadow: none;
	cursor: crosshair;
}
.k-image-file-preview .k-coords-input-thumb::after {
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
.k-image-file-preview:not([data-has-focus="true"]) .k-coords-input-thumb {
	display: none;
}

.k-image-file-preview-focus dd {
	display: flex;
	align-items: center;
}
.k-image-file-preview-focus .k-button {
	--button-color-back: var(--color-gray-800);
	--button-padding: var(--spacing-2);
	--button-color-back: var(--color-gray-800);
}
</style>
