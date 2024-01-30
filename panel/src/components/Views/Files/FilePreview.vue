<template>
	<div class="k-file-preview" :data-has-focus="Boolean(focus)">
		<!-- Thumb -->
		<div class="k-file-preview-thumb-column">
			<div class="k-file-preview-thumb">
				<!-- Image with focus picker -->
				<template v-if="image.src">
					<k-coords-input
						:disabled="!focusable"
						:value="focus"
						@input="setFocus($event)"
					>
						<img v-bind="image" @dragstart.prevent />
					</k-coords-input>

					<k-button
						icon="dots"
						size="xs"
						style="color: var(--color-gray-500)"
						@click="$refs.dropdown.toggle()"
					/>
					<k-dropdown-content ref="dropdown" :options="options" theme="light" />
				</template>

				<!-- Icon -->
				<k-icon
					v-else
					:color="$helper.color(image.color)"
					:type="image.icon"
					class="k-item-icon"
				/>
			</div>
		</div>

		<!-- Details -->
		<div class="k-file-preview-details">
			<dl>
				<div v-for="detail in details" :key="detail.title">
					<dt>{{ detail.title }}</dt>
					<dd>
						<k-link
							v-if="detail.link"
							:to="detail.link"
							tabindex="-1"
							target="_blank"
						>
							/{{ detail.text }}
						</k-link>
						<template v-else>
							{{ detail.text }}
						</template>
					</dd>
				</div>

				<div v-if="image.src" class="k-file-preview-focus-info">
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
				</div>
			</dl>
		</div>
	</div>
</template>

<script>
export default {
	props: {
		details: {
			default: () => [],
			type: Array
		},
		focus: {
			type: Object
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
				return this.$emit("focus", null);
			}

			this.$emit("focus", {
				x: focus.x.toFixed(1),
				y: focus.y.toFixed(1)
			});
		}
	}
};
</script>
<style>
.k-file-preview {
	display: grid;
	align-items: stretch;
	background: var(--color-gray-900);
	border-radius: var(--rounded-lg);
	margin-bottom: var(--spacing-12);
	overflow: hidden;
}

/* Thumb */
.k-file-preview-thumb-column {
	background: var(--pattern);
	aspect-ratio: 1/1;
}
.k-file-preview-thumb {
	display: flex;
	align-items: center;
	justify-content: center;
	height: 100%;
	padding: var(--spacing-12);
	container-type: size;
}

.k-file-preview-thumb img {
	width: auto;
	max-width: 100cqw;
	max-height: 100cqh;
}
.k-file-preview-thumb > .k-icon {
	--icon-size: 3rem;
}
.k-file-preview-thumb > .k-button {
	position: absolute;
	top: var(--spacing-2);
	inset-inline-start: var(--spacing-2);
}
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
	background: var(--color-white);
	border-radius: 50%;
}
.k-file-preview:not([data-has-focus="true"]) .k-coords-input-thumb {
	display: none;
}

/* Details */
.k-file-preview-details {
	display: grid;
}
.k-file-preview-details dl {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr));
	grid-gap: var(--spacing-6) var(--spacing-12);
	align-self: center;
	padding: var(--spacing-6);
	line-height: 1.5em;
	padding: var(--spacing-6);
}
.k-file-preview-details dt {
	font-size: var(--text-sm);
	font-weight: 500;
	font-weight: var(--font-semi);
	color: var(--color-gray-500);
	margin-bottom: var(--spacing-1);
}
.k-file-preview-details :where(dd, a) {
	font-size: var(--text-xs);
	color: rgb(255 255 255 / 0.5);
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	color: rgba(255, 255, 255, 0.75);
	font-size: var(--text-sm);
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

@container (min-width: 36rem) {
	.k-file-preview {
		grid-template-columns: 50% auto;
	}
	.k-file-preview-thumb-column {
		aspect-ratio: auto;
	}
}

@container (min-width: 65rem) {
	.k-file-preview {
		grid-template-columns: 33.333% auto;
	}
	.k-file-preview-thumb-column {
		aspect-ratio: 1/1;
	}
}
@container (min-width: 90rem) {
	.k-file-preview-layout {
		grid-template-columns: 25% auto;
	}
}
</style>
