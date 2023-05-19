<template>
	<k-field v-bind="$props" :input="_uid" class="k-link-field">
		<k-input v-bind="$props" :invalid="isInvalid" :icon="false" theme="field">
			<div class="k-link-input-header">
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
							v-for="(linkType, key) in types"
							:key="key"
							:icon="linkType.icon"
							@click="switchType(key)"
						>
							{{ linkType.label }}
						</k-dropdown-item>
					</k-dropdown-content>
				</k-dropdown>
				<template v-if="linkType === 'page' || linkType === 'file'">
					<div class="k-link-input-model" @click="toggle">
						<template v-if="model">
							<k-tag :removable="true" @remove="clear">
								{{ model.label }}
							</k-tag>
						</template>

						<template v-else>
							<k-button class="k-link-input-model-placeholder" @click="toggle">
								{{ currentType.placeholder }}
							</k-button>
						</template>

						<k-button class="k-link-input-model-toggle" icon="bars" />
					</div>
				</template>
				<component
					v-else
					ref="input"
					:is="'k-' + currentType.input + '-input'"
					:id="_uid"
					:pattern="currentType.pattern ?? null"
					:placeholder="currentType.placeholder"
					:value="linkValue"
					@invalid="onInvalid"
					@input="onInput"
				/>
			</div>
			<template v-if="linkType === 'page'">
				<div v-show="expanded" data-type="page" class="k-link-input-body">
					<k-page-tree
						:current="value"
						:root="false"
						@select="onInput($event.id)"
					/>
				</div>
			</template>
			<template v-else-if="linkType === 'file'">
				<div v-show="expanded" data-type="file" class="k-link-input-body">
					<k-file-browser :selected="value" @select="onInput($event.id)" />
				</div>
			</template>
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
			return this.types[this.linkType] || this.types["url"];
		},
		types() {
			return {
				url: {
					icon: "url",
					label: this.$t("url"),
					placeholder: this.$t("url.placeholder"),
					input: "url",
					schema: ""
				},
				page: {
					icon: "page",
					label: this.$t("page"),
					placeholder: "Select a page …",
					input: "text",
					schema: ""
				},
				file: {
					icon: "file",
					label: this.$t("file"),
					placeholder: "Select a file …",
					schema: ""
				},
				email: {
					icon: "email",
					label: this.$t("email"),
					placeholder: this.$t("email.placeholder"),
					input: "email",
					schema: "mailto:"
				},
				tel: {
					icon: "phone",
					label: "Phone",
					pattern: "[+]{0,1}[0-9]+",
					placeholder: "Enter a phone number …",
					input: "tel",
					schema: "tel:"
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
		detect(value) {
			value = value ?? "";

			if (
				value.startsWith("page://") === true ||
				value.startsWith("site://") ||
				value.startsWith("/")
			) {
				return {
					type: "page",
					link: value
				};
			}

			if (value.startsWith("file://") === true) {
				return {
					type: "file",
					link: value
				};
			}

			if (value.startsWith("tel:")) {
				return {
					type: "tel",
					link: value.replace(/^tel\:/, "")
				};
			}

			if (value.startsWith("mailto:")) {
				return {
					type: "email",
					link: value.replace(/^mailto\:/, "")
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
		async preview() {
			if (this.linkType === "page" && this.linkValue) {
				const page = await this.$api.pages.get(this.linkValue, {
					select: "id,title"
				});

				this.model = {
					label: page.title
				};
			} else if (this.linkType === "file" && this.linkValue) {
				const file = await this.$api.get("files/" + this.linkValue, {
					select: "id,filename"
				});
				this.model = {
					label: file.filename
				};
			} else {
				this.model = null;
			}
		},
		onInput(link) {
			const value = link.trim().length ? this.currentType.schema + link : "";
			this.$emit("input", value);
		},
		onInvalid(invalid) {
			this.isInvalid = invalid;
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
	grid-template-columns: max-content 1fr;
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
	display: inline-flex;
	align-items: center;
	background: var(--color-blue-200);
	padding-inline-start: var(--spacing-3);
	font-size: var(--text-sm);
	border-radius: var(--rounded-sm);
}
.k-link-input-model-placeholder.k-button {
	display: flex;
	flex-grow: 1;
	align-items: center;
	justify-content: start;
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
	grid-area: body;
	border-top: 1px solid var(--color-gray-300);
	background: var(--color-gray-100);
	overflow: auto;
	--tree-color-back: var(--color-gray-100);
	--tree-color-hover-back: var(--color-gray-200);
}

.k-link-input-body[data-type="page"] {
	padding: var(--spacing-2);
	padding-bottom: calc(var(--spacing-2) - 1px);
}

.k-link-input-body .k-file-browser-tree {
	border-right: 1px solid var(--color-gray-300);
}
.k-link-input-body .k-file-browser-items {
	background: var(--color-gray-100);
}
</style>
