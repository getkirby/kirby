<template>
	<k-field v-bind="$props" :input="_uid" class="k-link-field">
		<k-input v-bind="$props" :invalid="isInvalid" :icon="false" theme="field">
			<div class="k-link-input-header">
				<!-- Type selector -->
				<k-dropdown>
					<k-button
						class="k-link-input-toggle"
						:icon="currentType.icon"
						@click="$refs.types.toggle()"
					>
						{{ currentType.label }}
					</k-button>
					<k-dropdown-content ref="types">
						<k-dropdown-item
							v-for="(type, key) in types"
							:key="key"
							:icon="type.icon"
							@click="switchType(key)"
						>
							{{ type.label }}
						</k-dropdown-item>
					</k-dropdown-content>
				</k-dropdown>

				<!-- Input -->
				<div
					v-if="linkType === 'page' || linkType === 'file'"
					class="k-link-input-model"
					@click="toggle"
				>
					<k-tag
						v-if="model"
						:removable="true"
						class="k-link-input-model-preview"
						@remove="clear"
					>
						<k-item-image
							v-if="model.image"
							:image="{ ...model.image, cover: true, back: 'gray-200' }"
							class="k-link-input-model-preview-image"
						/>

						{{ model.label }}
					</k-tag>
					<k-button v-else class="k-link-input-model-placeholder">
						{{ currentType.placeholder }}
					</k-button>

					<k-button class="k-link-input-model-toggle" icon="bars" />
				</div>
				<component
					v-else
					:is="'k-' + currentType.input + '-input'"
					:id="_uid"
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
						:current="getPageUUID(value)"
						:root="false"
						@select="onInput($event.id)"
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
					@select="onInput($event.id)"
				/>
			</div>
		</k-input>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";

/**
 * Have a look at `<k-field>` and `<k-input>`
 * @example <k-link-field :value="link" @input="link = $event" name="link" label="Link" />
 */
export default {
	mixins: [Field, Input],
	inheritAttrs: false,
	props: {
		value: {
			default: "",
			type: String
		}
	},
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
			return this.types[this.linkType] ?? this.types["url"];
		},
		types() {
			return {
				url: {
					icon: "url",
					label: this.$t("url"),
					placeholder: this.$t("url.placeholder"),
					input: "url",
					value: (value) => value
				},
				page: {
					icon: "page",
					label: this.$t("page"),
					placeholder: this.$t("select") + " …",
					input: "text",
					value: (value) => value
				},
				file: {
					icon: "file",
					label: this.$t("file"),
					placeholder: this.$t("select") + " …",
					value: (value) => value
				},
				email: {
					icon: "email",
					label: this.$t("email"),
					placeholder: this.$t("email.placeholder"),
					input: "email",
					value: (value) => "mailto:" + value
				},
				tel: {
					icon: "phone",
					label: "Phone",
					pattern: "[+]{0,1}[0-9]+",
					placeholder: "Enter a phone number …",
					input: "tel",
					value: (value) => "tel:" + value
				}
			};
		}
	},
	watch: {
		value: {
			handler(value, old) {
				const parts = this.detect(value);

				this.linkType = this.linkType ?? parts.type;
				this.linkValue = parts.link;

				if (value !== old) {
					this.preview();
				}
			},
			immediate: true
		}
	},
	methods: {
		clear() {
			this.$emit("input", "");
			this.expanded = false;
		},
		detect(value = "") {
			if (this.isPageUUID(value) === true) {
				return {
					type: "page",
					link: value
				};
			}

			if (this.isFileUUID(value) === true) {
				return {
					type: "file",
					link: value
				};
			}

			if (value.startsWith("tel:")) {
				return {
					type: "tel",
					link: value.replace(/^tel:/, "")
				};
			}

			if (value.startsWith("mailto:")) {
				return {
					type: "email",
					link: value.replace(/^mailto:/, "")
				};
			}

			return {
				type: "url",
				link: value
			};
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
				value.startsWith("page://") === true ||
				value.startsWith("/@/page/") === true
			);
		},
		onInput(link) {
			const value = link.trim();

			if (!value.length) {
				return this.$emit("input", "");
			}

			this.$emit("input", this.currentType.value(value));
		},
		onInvalid(invalid) {
			this.isInvalid = invalid;
		},
		async preview() {
			if (this.linkType === "page" && this.linkValue) {
				this.model = await this.previewForPage(this.linkValue);
			} else if (this.linkType === "file" && this.linkValue) {
				this.model = await this.previewForFile(this.linkValue);
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
		switchType(type) {
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
			this.$nextTick(() => {
				this.focus();
			});
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
	height: var(--field-input-height);
	grid-area: header;
}

.k-link-input-toggle.k-button {
	display: flex;
	align-items: center;
	padding: 0 1.325rem 0 0.375rem;
	height: var(--height-sm);
	border-radius: var(--rounded-sm);
	margin-inline-start: 0.25rem;
	gap: 0.25rem;
	background: var(--color-gray-200);
}
.k-link-input-toggle.k-button .k-button-text {
	padding-inline-start: var(--spacing-1);
}

.k-link-input-toggle .k-button-text::after {
	position: absolute;
	top: 50%;
	right: 0.5rem;
	margin-top: -2px;
	content: "";
	border-top: 4px solid var(--color-black);
	border-inline-start: 4px solid transparent;
	border-inline-end: 4px solid transparent;
}

.k-link-input-model {
	display: flex;
	overflow: hidden;
	justify-content: space-between;
	height: var(--height-sm);
	margin-inline-end: var(--spacing-1);

	--tag-color-back: var(--color-gray-200);
	--tag-color-text: var(--color-black);
	--tag-color-focus-back: var(--tag-color-back);
	--tag-color-focus-text: var(--tag-color-text);
	--tag-rounded: var(--rounded-sm);
}
.k-link-input-model-preview {
	overflow: hidden;
	white-space: nowrap;
}
.k-link-input-model-preview .k-tag-text {
	display: flex;
	gap: 0.5rem;
	align-items: center;
	overflow: hidden;
	text-overflow: ellipsis;
}
.k-link-input-model-preview-image {
	height: calc(var(--height-sm) - 0.5rem);
	aspect-ratio: 1/1;
	border-radius: 1px;
}
.k-link-input-model-preview .k-tag-text:has(.k-link-input-model-preview-image) {
	padding-inline-start: 0.25rem;
}
.k-link-input-model-placeholder.k-button {
	display: flex;
	flex-grow: 1;
	overflow: hidden;
	white-space: nowrap;
	align-items: center;
	justify-content: flex-start;
	height: var(--height-sm);
	font-size: var(--text-base);
	padding-inline: var(--spacing-2);
	color: var(--color-gray-600);
}
.k-link-input-model-toggle {
	display: flex;
	height: var(--height-sm);
	width: var(--height-sm);
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
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
