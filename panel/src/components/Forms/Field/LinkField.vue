<template>
	<k-field v-bind="$props" :input="_uid" class="k-link-field">
		<k-input v-bind="$props" :icon="false" theme="field">
			<k-array-input
				v-bind="{
					name,
					required
				}"
				:value="JSON.stringify(value)"
				class="k-link-input-header"
			>
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
					:id="_uid"
					ref="input"
					:required="required"
					:disabled="disabled"
					:pattern="currentType.pattern ?? null"
					:placeholder="currentType.placeholder"
					:value="linkValue"
					@input="onInput"
				/>
			</k-array-input>

			<!-- Page or file browser -->
			<div
				v-if="linkType === 'page'"
				v-show="expanded"
				data-type="page"
				class="k-link-input-body"
			>
				<div class="k-page-browser">
					<k-page-tree
						:current="getPageUUID(value)"
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
					:selected="getFileUUID(value)"
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
			expanded: false
		};
	},
	computed: {
		currentType() {
			return (
				this.activeTypes[this.linkType] ?? Object.values(this.activeTypes)[0]
			);
		},
		availableTypes() {
			return {
				url: {
					detect: (value) => {
						return /^(http|https):\/\//.test(value);
					},
					icon: "url",
					label: this.$t("url"),
					link: (value) => value,
					placeholder: this.$t("url.placeholder"),
					input: "url",
					value: (value) => value
				},
				page: {
					detect: (value) => {
						return this.isPageUUID(value) === true;
					},
					icon: "page",
					label: this.$t("page"),
					link: (value) => value,
					placeholder: this.$t("select") + " …",
					input: "text",
					value: (value) => value
				},
				file: {
					detect: (value) => {
						return this.isFileUUID(value) === true;
					},
					icon: "file",
					label: this.$t("file"),
					link: (value) => value,
					placeholder: this.$t("select") + " …",
					value: (value) => value
				},
				email: {
					detect: (value) => {
						return value.startsWith("mailto:");
					},
					icon: "email",
					label: this.$t("email"),
					link: (value) => value.replace(/^mailto:/, ""),
					placeholder: this.$t("email.placeholder"),
					input: "email",
					value: (value) => "mailto:" + value
				},
				tel: {
					detect: (value) => {
						return value.startsWith("tel:");
					},
					icon: "phone",
					label: this.$t("tel"),
					link: (value) => value.replace(/^tel:/, ""),
					pattern: "[+]{0,1}[0-9]+",
					placeholder: this.$t("tel.placeholder"),
					input: "tel",
					value: (value) => "tel:" + value
				},
				anchor: {
					detect: (value) => {
						return value.startsWith("#");
					},
					icon: "anchor",
					label: "Anchor",
					link: (value) => value,
					pattern: "^#.+",
					placeholder: "#element",
					input: "text",
					value: (value) => value
				},
				custom: {
					detect: () => true,
					icon: "title",
					label: this.$t("custom"),
					link: (value) => value,
					input: "text",
					value: (value) => value
				}
			};
		},
		activeTypes() {
			if (!this.options?.length) {
				return this.availableTypes;
			}

			const active = {};

			for (const type of this.options) {
				active[type] = this.availableTypes[type];
			}

			return active;
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
			handler(value, old) {
				if (value === old) {
					return;
				}

				const parts = this.detect(value);

				this.linkType = this.linkType ?? parts.type;
				this.linkValue = parts.link;

				if (value !== old) {
					this.preview(parts);
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
		detect(value) {
			value = value ?? "";

			if (value.length === 0) {
				return {
					type: "url",
					link: ""
				};
			}

			for (const type in this.availableTypes) {
				if (this.availableTypes[type].detect(value) === true) {
					return {
						type: type,
						link: this.availableTypes[type].link(value)
					};
				}
			}
		},
		focus() {
			this.$refs.input?.focus();
		},
		getFileUUID(value) {
			return value.replace("/@/file/", "file://");
		},
		getPageUUID(value) {
			return value.replace("/@/page/", "page://");
		},
		isFileUUID(value) {
			return (
				value.startsWith("file://") === true ||
				value.startsWith("/@/file/") === true
			);
		},
		isPageUUID(value) {
			return (
				value === "site://" ||
				value.startsWith("page://") === true ||
				value.startsWith("/@/page/") === true
			);
		},
		onInput(link) {
			const value = link?.trim() ?? "";

			if (!value.length) {
				return this.$emit("input", "");
			}

			this.$emit("input", this.currentType.value(value));
		},
		onOutsideClick(event) {
			if (this.$el.contains(event.target) === false) {
				this.expanded = false;
			}
		},
		async preview({ type, link }) {
			if (type === "page" && link) {
				this.model = await this.previewForPage(link);
			} else if (type === "file" && link) {
				this.model = await this.previewForFile(link);
			} else if (link) {
				this.model = {
					label: link
				};
			} else {
				this.model = null;
			}
		},
		async previewForFile(id) {
			try {
				const file = await this.$api.files.get(null, id, {
					select: "filename, panelImage"
				});

				return {
					label: file.filename,
					image: file.panelImage
				};
			} catch (e) {
				return null;
			}
		},
		async previewForPage(id) {
			if (id === "site://") {
				return {
					label: this.$t("view.site")
				};
			}

			try {
				const page = await this.$api.pages.get(id, {
					select: "title"
				});

				return {
					label: page.title
				};
			} catch (e) {
				return null;
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
