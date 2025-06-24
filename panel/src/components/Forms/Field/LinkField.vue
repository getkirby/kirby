<template>
	<k-field
		v-bind="$props"
		:class="['k-link-field', $attrs.class]"
		:input="id"
		:style="$attrs.style"
	>
		<k-input v-bind="$props" :icon="false">
			<div class="k-link-input-header">
				<!-- Type selector -->
				<k-button
					class="k-link-input-toggle"
					:disabled="disabled"
					:dropdown="!disabled && activeTypesOptions.length > 1"
					:icon="currentType.icon"
					variant="filled"
					@click="
						activeTypesOptions.length > 1 ? $refs.types.toggle() : toggle()
					"
				>
					{{ currentType.label }}
				</k-button>
				<k-dropdown-content ref="types" :options="activeTypesOptions" />

				<!-- Input -->
				<div
					v-if="currentType.id === 'page' || currentType.id === 'file'"
					class="k-link-input-model"
					@click="toggle"
				>
					<k-link-field-preview
						:removable="true"
						:type="currentType.id"
						:value="value"
						@remove="removeModel"
					>
						<template #placeholder>
							<k-button class="k-link-input-model-placeholder">
								{{ currentType.placeholder }}
							</k-button>
						</template>
					</k-link-field-preview>
					<k-button class="k-link-input-model-toggle" icon="bars" />
				</div>

				<component
					:is="'k-' + currentType.input + '-input'"
					v-else
					:id="id"
					ref="input"
					:disabled="disabled"
					:pattern="currentType.pattern ?? null"
					:placeholder="currentType.placeholder"
					:required="required"
					:value="linkValue"
					@input="onInput"
				/>
			</div>

			<!-- Page or file browser -->
			<div
				v-if="currentType.id === 'page'"
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
				v-else-if="currentType.id === 'file'"
				v-show="expanded"
				data-type="file"
				class="k-link-input-body"
			>
				<k-file-browser
					:opened="$panel.view.props.model.uuid ?? $panel.view.props.model.id"
					:selected="$helper.link.getFileUUID(value)"
					@select="selectModel($event)"
				/>
			</div>
		</k-input>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputComponentProps } from "../Input.vue";
import { props as InputMixinProps } from "@/mixins/input.js";
import { options } from "@/mixins/props.js";

export const props = {
	mixins: [FieldProps, InputComponentProps, InputMixinProps, options],
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
			/**
			 * Stores the currently detected link type. The currentType
			 * object is computed from this
			 */
			linkType: null,
			/**
			 * The link value holds the value that is visible in the input
			 * E.g. for the email type, the actually stored value would be
			 * prefixed by mailto: but the linkValue would be without prefix
			 */
			linkValue: null,
			/**
			 * Open/close state for the file or page browser
			 */
			expanded: false
		};
	},
	computed: {
		/**
		 * Returns all available link types as defined
		 * by the options prop
		 */
		activeTypes() {
			return this.$helper.link.types(this.options);
		},
		/**
		 * Converts all active types to
		 * dropdown options
		 */
		activeTypesOptions() {
			const options = [];

			for (const type in this.activeTypes) {
				options.push({
					click: () => this.switchType(type),
					current: type === this.currentType.id,
					icon: this.activeTypes[type].icon,
					label: this.activeTypes[type].label
				});
			}

			return options;
		},
		/**
		 * Returns the full type as defined in
		 * the helpers/link.js types object. Falls back
		 * to the first available type.
		 */
		currentType() {
			return (
				this.activeTypes[this.linkType] ?? Object.values(this.activeTypes)[0]
			);
		}
	},
	watch: {
		/**
		 * When the value changes from the outside. E.g. by reverting
		 * changes or mounting the field for the first time, the link type
		 * and link value need to be detected
		 */
		value: {
			async handler(value, old) {
				if (value === old || value === this.linkValue) {
					return;
				}

				const parts = this.$helper.link.detect(value, this.activeTypes);

				if (parts) {
					this.linkType = parts.type;
					this.linkValue = parts.link;
				}
			},
			immediate: true
		}
	},
	mounted() {
		this.$events.on("click", this.onOutsideClick);
	},
	destroyed() {
		this.$events.off("click", this.onOutsideClick);
	},
	methods: {
		clear() {
			this.linkValue = "";
			this.$emit("input", "");
		},
		focus() {
			this.$refs.input?.focus();
		},
		onInput(link) {
			const value = link?.trim() ?? "";

			this.linkType ??= this.currentType.id;
			this.linkValue = value;

			if (!value.length) {
				return this.clear();
			}

			this.$emit("input", this.currentType.value(value));
		},
		onOutsideClick(event) {
			if (this.$el.contains(event.target) === false) {
				this.expanded = false;
			}
		},
		removeModel() {
			this.clear();
			this.expanded = false;
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
			// avoid unnecessary switching
			if (type === this.currentType.id) {
				return;
			}

			// set the new type
			this.linkType = type;

			// remove the value
			this.clear();

			// show the file or page browser
			if (this.currentType.id === "page" || this.currentType.id === "file") {
				this.expanded = true;
			} else {
				this.expanded = false;
			}

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
	--button-color-back: var(--panel-color-back);
	margin-inline-start: 0.25rem;
}

.k-link-input-model {
	display: flex;
	justify-content: space-between;
	margin-inline-end: var(--spacing-1);
}
.k-link-input-model-placeholder.k-button {
	--button-align: flex-start;
	--button-color-text: var(--color-gray-600);
	--button-height: var(--height-sm);
	--button-padding: var(--spacing-2);
	--button-rounded: var(--rounded-sm);
	flex-grow: 1;
	overflow: hidden;
	white-space: nowrap;
	align-items: center;
}

.k-link-field .k-link-field-preview {
	--tag-height: var(--height-sm);
	padding-inline: 0;
}
.k-link-field .k-link-field-preview .k-tag:focus {
	outline: 0;
}
.k-link-field .k-link-field-preview .k-tag:focus-visible {
	outline: var(--outline);
}
.k-link-field .k-link-field-preview .k-tag-text {
	font-size: var(--text-sm);
}

.k-link-input-model-toggle {
	align-self: center;
	--button-height: var(--height-sm);
	--button-width: var(--height-sm);
	--button-rounded: var(--rounded-sm);
}

.k-link-input-body {
	display: grid;
	overflow: hidden;
	border-top: 1px solid var(--color-border);
	background: var(--input-color-back);
	--tree-color-back: var(--input-color-back);
	--tree-branch-color-back: var(--input-color-back);
	--tree-branch-hover-color-back: var(--panel-color-back);
}

.k-link-input-body[data-type="page"] .k-page-browser {
	padding: var(--spacing-2);
	padding-bottom: calc(var(--spacing-2) - 1px);
	width: 100%;
	container-type: inline-size;
	overflow: auto;
}
.k-link-field .k-tags-field-preview {
	--tag-rounded: var(--rounded-sm);
	--tag-size: var(--height-sm);
	padding-inline: 0;
}

.k-link-field[data-disabled="true"] .k-link-input-model-placeholder {
	display: none;
}
.k-link-field[data-disabled="true"] input::placeholder {
	opacity: 0;
}
</style>
