<template>
	<k-inputbox v-bind="$props" type="link">
		<template slot="body">
			<div class="k-link-inputbox-header">
				<k-button
					class="k-link-inputbox-toggle"
					:autofocus="autofocus"
					:disabled="disabled"
					:dropdown="true"
					:icon="currentType.icon"
					size="sm"
					variant="filled"
					@click="$refs.types.toggle()"
				>
					{{ currentType.label }}
				</k-button>
				<k-dropdown-content ref="types">
					<k-dropdown-item
						v-for="(type, key) in activeTypes"
						:key="key"
						:current="key === linkType"
						:icon="type.icon"
						@click="switchType(key)"
					>
						{{ type.label }}
					</k-dropdown-item>
				</k-dropdown-content>

				<k-inputbox-element v-if="linkType === 'page' || linkType === 'file'">
					<k-tag
						v-if="model"
						:id="id"
						:image="
							model.image
								? {
										...model.image,
										cover: true,
										back: 'gray-100'
								  }
								: null
						"
						:removable="!disabled"
						:data-has-image="model.image"
						class="k-link-inputbox-model-preview"
						@click.native="toggle"
						@remove="clear"
					>
						{{ model.label }}
					</k-tag>
					<k-button
						v-else
						:id="id"
						class="k-link-inputbox-model-placeholder"
						@click="toggle"
					>
						{{ currentType.placeholder }}
					</k-button>
				</k-inputbox-element>
				<k-inputbox-element v-else>
					<component
						:is="'k-' + currentType.input + '-input'"
						ref="input"
						:id="id"
						:pattern="currentType.pattern ?? null"
						:placeholder="currentType.placeholder"
						:required="required"
						:value="linkValue"
						@invalid="onInvalid"
						@input="onInput"
					/>
				</k-inputbox-element>

				<k-inputbox-icon v-if="linkType === 'page' || linkType === 'file'">
					<k-button
						class="k-inputbox-icon-button"
						icon="menu"
						@click="toggle"
					/>
				</k-inputbox-icon>
			</div>

			<!-- Page or file browser -->
			<div
				v-if="linkType === 'page'"
				v-show="expanded"
				data-type="page"
				class="k-link-inputbox-body"
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
				class="k-link-inputbox-body"
			>
				<k-file-browser
					:selected="getFileUUID(value)"
					@select="selectModel($event)"
				/>
			</div>
		</template>
	</k-inputbox>
</template>

<script>
import { autofocus, id, required } from "@/mixins/props.js";
import { props as InputboxProps } from "../Inputbox.vue";

export const props = {
	mixins: [InputboxProps, autofocus, id, required],
	props: {
		options: Array,
		value: {
			default: "",
			type: String
		}
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"],
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
		activeTypes() {
			if (!this.options) {
				return this.availableTypes;
			}

			const available = {};

			for (const type of this.options) {
				available[type] = this.availableTypes[type];
			}

			return available;
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
		currentType() {
			return (
				this.activeTypes[this.linkType] ?? Object.values(this.activeTypes)[0]
			);
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
		onInvalid(invalid) {
			this.isInvalid = !!invalid;
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
.k-link-inputbox {
	flex-direction: column;
	align-items: start;
}
.k-link-inputbox-header {
	--button-rounded: var(--rounded-sm);
	width: 100%;
	flex-grow: 1;
	display: grid;
	grid-template-columns: max-content minmax(0, 1fr) max-content;
	align-items: center;
	gap: 0.25rem;
	height: var(--input-height);
}
.k-link-inputbox-toggle.k-button {
	--button-color-back: var(--color-gray-250);
	margin-inline-start: var(--spacing-1);
}

.k-link-inputbox-model-preview.k-tag {
	--tag-height: var(--height-sm);
	--tag-color-back: var(--color-gray-250);
	--tag-color-text: var(--color-black);
}

.k-link-inputbox-model-preview-image {
	height: calc(var(--height-sm) - 0.5rem);
	border-radius: var(--rounded-sm);
}

.k-link-inputbox-model-placeholder.k-button {
	--button-align: flex-start;
	--button-color-text: var(--color-gray-600);
	--button-height: var(--input-height);
	--button-padding: var(--spacing-2);
	flex-grow: 1;
	overflow: hidden;
	white-space: nowrap;
}

.k-link-inputbox-body {
	width: 100%;
	display: grid;
	overflow: hidden;
	border-top: 1px solid var(--color-gray-300);
	background: var(--color-gray-100);
	--tree-color-back: var(--color-gray-100);
	--tree-color-hover-back: var(--color-gray-200);
}

.k-link-inputbox-body[data-type="page"] .k-page-browser {
	padding: var(--spacing-2);
	padding-bottom: calc(var(--spacing-2) - 1px);
	width: 100%;
	container-type: inline-size;
	overflow: auto;
}
</style>
