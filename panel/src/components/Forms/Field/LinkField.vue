<template>
	<k-field v-bind="$props" :input="id" class="k-link-field">
		<k-input v-bind="$props" :invalid="isInvalid" :icon="false">
			<div class="k-link-input-header">
				<!-- Type selector -->
				<k-button
					class="k-link-input-toggle"
					:disabled="disabled"
					:dropdown="true"
					:icon="currentType.icon"
					variant="filled"
					@click="$refs.types.toggle()"
				>
					{{ currentType.label }}
				</k-button>
				<k-dropdown-content ref="types" :options="activeTypesOptions" />

				<!-- Input -->
				<div
					v-if="linkType === 'page' || linkType === 'file'"
					class="k-link-input-model"
					@click="toggle"
				>
					<k-tag
						v-if="model"
						:image="
							model.image
								? { ...model.image, cover: true, back: 'gray-200' }
								: null
						"
						:removable="!disabled"
						class="k-link-input-model-preview"
						@remove="clear"
					>
						{{ model.label }}
					</k-tag>
					<k-button v-else class="k-link-input-model-placeholder">
						{{ currentType.placeholder }}
					</k-button>

					<k-button class="k-link-input-model-toggle" icon="bars" />
				</div>
				<component
					:is="'k-' + currentType.input + '-input'"
					v-else
					:id="id"
					ref="input"
					:pattern="currentType.pattern ?? null"
					:placeholder="currentType.placeholder"
					:value="linkValue"
					@invalid="onInvalid"
					@input="onInput"
				/>
			</div>

			<!-- Page or file browser -->
			<div
				v-if="linkType === 'page'"
				v-show="expanded"
				data-type="page"
				class="k-link-input-body"
			>
				<div class="k-page-browser">
					<k-page-tree
						:current="$helper.link.getPageUUID(value)"
						:root="false"
						@select="selectModel($event)"
					/>
				</div>
			</div>
			<div
				v-else-if="linkType === 'file'"
				v-show="expanded"
				data-type="file"
				class="k-link-input-body"
			>
				<k-file-browser
					:selected="$helper.link.getFileUUID(value)"
					@select="selectModel($event)"
				/>
			</div>
		</k-input>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputProps } from "../Input.vue";
import { options } from "@/mixins/props.js";

export const props = {
	mixins: [FieldProps, InputProps, options],
	props: {
		value: {
			default: "",
			type: String
		}
	}
};

/**
 * @since 4.0.0
 * @example <k-link-field :value="link" @input="link = $event" name="link" label="Link" />
 */
export default {
	mixins: [props],
	inheritAttrs: false,
	data() {
		return {
			model: null,
			linkType: null,
			linkValue: null,
			expanded: false,
			isInvalid: false
		};
	},
	computed: {
		currentType() {
			return (
				this.activeTypes[this.linkType] ?? Object.values(this.activeTypes)[0]
			);
		},
		activeTypes() {
			return this.$helper.link.types(this.options);
		},
		activeTypesOptions() {
			const options = [];

			for (const type in this.activeTypes) {
				options.push({
					click: () => this.switchType(type),
					current: type === this.linkType,
					icon: this.activeTypes[type].icon,
					label: this.activeTypes[type].label
				});
			}

			return options;
		}
	},
	watch: {
		value: {
			async handler(value, old) {
				if (value === old) {
					return;
				}

				const parts = this.$helper.link.detect(value, this.activeTypes);
				this.linkType = this.linkType ?? parts.type;
				this.linkValue = parts.link;

				if (value !== old) {
					this.model = await this.$helper.link.preview(parts);
				}
			},
			immediate: true
		}
	},
	created() {
		this.$events.on("click", this.onOutsideClick);
	},
	destroyed() {
		this.$events.off("click", this.onOutsideClick);
	},
	methods: {
		clear() {
			this.$emit("input", "");
			this.expanded = false;
		},
		focus() {
			this.$refs.input?.focus();
		},
		onInput(link) {
			const value = link?.trim() ?? "";

			if (!value.length) {
				return this.$emit("input", "");
			}

			this.$emit("input", this.currentType.value(value));
		},
		onInvalid(invalid) {
			this.isInvalid = !!invalid;
		},
		onOutsideClick(event) {
			if (this.$el.contains(event.target) === false) {
				this.expanded = false;
			}
		},
		selectModel(model) {
			if (model.uuid) {
				this.onInput(model.uuid);
				return;
			}

			this.switchType("url");
			this.onInput(model.url);
		},
		async switchType(type) {
			if (type === this.linkType) {
				return;
			}

			this.isInvalid = false;
			this.linkType = type;
			this.linkValue = "";

			if (this.linkType === "page" || this.linkType === "file") {
				this.expanded = true;
			} else {
				this.expanded = false;
			}

			this.$emit("input", "");
			await this.$nextTick();
			this.focus();
		},
		toggle() {
			this.expanded = !this.expanded;
		}
	}
};
</script>

<style>
.k-link-input-header {
	display: grid;
	grid-template-columns: max-content minmax(0, 1fr);
	align-items: center;
	gap: 0.25rem;
	height: var(--input-height);
	grid-area: header;
}

.k-link-input-toggle.k-button {
	--button-height: var(--height-sm);
	--button-rounded: var(--rounded-sm);
	--button-color-back: var(--color-gray-200);
	margin-inline-start: 0.25rem;
}

.k-link-input-model {
	display: flex;
	overflow: hidden;
	justify-content: space-between;
	margin-inline-end: var(--spacing-1);

	--tag-height: var(--height-sm);
	--tag-color-back: var(--color-gray-200);
	--tag-color-text: var(--color-black);
	--tag-color-toggle: var(--tag-color-text);
	--tag-color-toggle-border: var(--color-gray-300);
	--tag-color-focus-back: var(--tag-color-back);
	--tag-color-focus-text: var(--tag-color-text);
	--tag-rounded: var(--rounded-sm);
}
.k-link-input-model-preview,
.k-link-input-model-preview .k-tag-text {
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
}
.k-link-input-model-placeholder.k-button {
	--button-align: flex-start;
	--button-color-text: var(--color-gray-600);
	--button-height: var(--height-sm);
	--button-padding: var(--spacing-2);
	flex-grow: 1;
	overflow: hidden;
	white-space: nowrap;
	align-items: center;
}
.k-link-input-model-toggle {
	--button-height: var(--height-sm);
	--button-width: var(--height-sm);
}

.k-link-input-body {
	display: grid;
	overflow: hidden;
	border-top: 1px solid var(--color-gray-300);
	background: var(--color-gray-100);
	--tree-color-back: var(--color-gray-100);
	--tree-color-hover-back: var(--color-gray-200);
}

.k-link-input-body[data-type="page"] .k-page-browser {
	padding: var(--spacing-2);
	padding-bottom: calc(var(--spacing-2) - 1px);
	width: 100%;
	container-type: inline-size;
	overflow: auto;
}
</style>
