<template>
	<div class="k-file-preview" :data-has-focus="hasFocus">
		<k-view class="k-file-preview-layout">
			<!-- Thumb -->
			<div class="k-file-preview-thumb">
				<!-- Image with focus picker -->
				<template v-if="image.src">
					<k-coords
						:aria-disabled="!focusable"
						:x="focus?.x"
						:y="focus?.y"
						@input="setFocus($event.detail)"
					>
						<img v-bind="image" @dragstart.prevent />
					</k-coords>

					<k-dropdown>
						<k-button
							icon="dots"
							size="xs"
							style="color: var(--color-gray-500)"
							@click="$refs.dropdown.toggle()"
						/>
						<k-dropdown-content
							ref="dropdown"
							:options="options"
							theme="light"
						/>
					</k-dropdown>
				</template>

				<!-- Icon -->
				<k-icon
					v-else
					:color="$helper.color(image.color)"
					:type="image.icon"
					class="k-item-icon"
				/>
			</div>

			<!-- Details -->
			<div class="k-file-preview-details">
				<dl v-for="detail in details" :key="detail.title">
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
				</dl>

				<dl v-if="image.src" class="k-file-preview-focus-info">
					<dt>{{ $t("file.focus.title") }}</dt>
					<dd>
						<k-button
							v-if="focusable"
							:icon="hasFocus ? 'cancel-small' : 'preview'"
							:title="hasFocus ? $t('file.focus.reset') : undefined"
							size="xs"
							variant="filled"
							@click="setFocus(hasFocus ? undefined : '50% 50%')"
						>
							<template v-if="hasFocus">
								{{ focus.x }}% {{ focus.y }}%
							</template>
							<template v-else-if="focusable">
								{{ $t("file.focus.placeholder") }}
							</template>
						</k-button>
						<template v-else-if="hasFocus">
							{{ focus.x }}% {{ focus.y }}%
						</template>
						<template v-else>–</template>
					</dd>
				</dl>
			</div>
		</k-view>
	</div>
</template>

<script>
export default {
	props: {
		details: Array,
		focusable: Boolean,
		image: Object,
		url: String
	},
	computed: {
		focus() {
			const focus = this.$store.getters["content/values"]()["focus"];

			if (!focus) {
				return;
			}

			const [x, y] = focus.replaceAll("%", "").split(" ");
			return { x: parseFloat(x), y: parseFloat(y) };
		},
		hasFocus() {
			return this.focus?.x !== undefined && this.focus?.y !== undefined;
		},
		options() {
			const options = [
				{
					icon: "open",
					text: this.$t("open"),
					link: this.url,
					target: "_blank"
				}
			];
			if (this.image.src) {
				options.push({
					icon: "cancel",
					text: this.$t("file.focus.reset"),
					disabled: Boolean(this.focus) === false,
					click: this.onFocus
				});
			}
			return options;
		},
		storeId() {
			return this.$store.getters["content/id"](null, true);
		}
	},
	methods: {
		setFocus(focus) {
			if (this.$helper.object.isObject(focus) === true) {
				focus = `${focus.x.toFixed(1)}% ${focus.y.toFixed(1)}%`;
			}

			// TODO: always write to default language
			this.$store.dispatch("content/update", ["focus", focus]);
		}
	}
};
</script>
<style>
.k-file-preview {
	background: var(--color-gray-800);
}
.k-file-preview-layout {
	display: grid;
	align-items: stretch;
	border-radius: var(--rounded-lg);
	margin-bottom: var(--spacing-6);
	overflow: hidden;
}
.k-file-preview-layout > * {
	min-width: 0;
}

/* Thumb */
.k-file-preview-thumb {
	--icon-size: 2rem;
	display: grid;
	place-items: center;
	aspect-ratio: 1/1;
	padding: var(--spacing-6);
	background: var(--bg-pattern);
	container-type: size;
}

.k-file-preview .k-coords {
	cursor: crosshair;
}
.k-file-preview-thumb img {
	max-width: 100cqw;
	max-height: 100cqh;
}
.k-file-preview .k-coords-thumb {
	--range-thumb-height: 1.25rem;
	background: hsl(216 60% 60% / 0.75);
	box-shadow: none;
}
.k-file-preview .k-coords-thumb::after {
	content: "";
	width: 0.4rem;
	height: 0.4rem;
	border-radius: 50%;
	background: var(--color-white);
	position: absolute;
	top: calc(50% - 0.2rem);
	inset-inline-start: calc(50% - 0.2rem);
}
.k-file-preview:not([data-has-focus="true"]) .k-coords-thumb {
	display: none;
}
.k-file-preview-icon {
	--icon-size: 3rem;
}
.k-file-preview-thumb .k-dropdown {
	position: absolute;
	top: var(--spacing-2);
	inset-inline-start: var(--spacing-2);
}

/* Details */
.k-file-preview-details {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr));
	grid-gap: var(--spacing-6) var(--spacing-12);
	padding: var(--spacing-6);
	line-height: 1.5em;
	align-self: center;
	padding: var(--spacing-6);
}
.k-file-preview-details dt {
	font-size: var(--text-sm);
	font-weight: 500;
	font-weight: var(--font-semi);
	color: var(--color-gray-500);
	margin-bottom: var(--spacing-2);
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
	height: 24px;
	background: var(--color-gray-700);
	padding: 0.25rem;
	border-radius: var(--rounded);
	font-size: var(--text-xs);
	line-height: 1;
}
.k-file-preview-focus-info .k-button:hover {
	background: var(--color-gray-600);
}

@media screen and (min-width: 36rem) {
	.k-file-preview-layout {
		grid-template-columns: 50% auto;
	}
	.k-file-preview-thumb {
		aspect-ratio: auto;
	}
}

@media screen and (min-width: 65rem) {
	.k-file-preview-thumb {
		aspect-ratio: 1/1;
	}
	.k-file-preview-layout {
		grid-template-columns: 33.333% auto;
	}
}
@media screen and (min-width: 90rem) {
	.k-file-preview-layout {
		grid-template-columns: 25% auto;
	}
}
</style>
