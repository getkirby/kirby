<template>
	<k-field v-bind="$props" :input="_uid" class="k-link-field">
		<k-input v-bind="$props" :invalid="isInvalid" theme="field">
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
				<component
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
				<div class="k-link-input-body">
					<k-page-tree :current="value" @select="onInput($event.uuid)" />
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
			linkType: null,
			linkValue: null,
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
					input: "url",
					label: this.$t("url"),
					placeholder: this.$t("url.placeholder"),
					input: "url",
					schema: ""
				},
				email: {
					icon: "email",
					input: "email",
					label: this.$t("email"),
					placeholder: this.$t("email.placeholder"),
					input: "email",
					schema: "mailto:"
				},
				tel: {
					icon: "phone",
					input: "tel",
					label: "Phone",
					pattern: "[+]{0,1}[0-9]+",
					placeholder: "Enter a phone number …",
					input: "tel",
					schema: "tel:"
				},
				page: {
					icon: "page",
					input: "text",
					label: this.$t("page"),
					placeholder: "Select a page …",
					input: "text",
					schema: ""
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
			},
			immediate: true
		}
	},
	methods: {
		detect(value) {
			value = value ?? "";

			if (value.startsWith("page://") === true || value.startsWith("site://")) {
				return {
					type: "page",
					link: value
				};
			}

			if (value.startsWith("tel:")) {
				console.log(value);

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
		onInput(link) {
			const value = link.trim().length ? this.currentType.schema + link : "";
			this.$emit("input", value);
		},
		onInvalid(invalid) {
			this.isInvalid = invalid;
		},
		focus() {
			this.$refs.input?.focus();
		},
		switchType(type) {
			if (type === this.linkType) {
				return;
			}

			this.linkType = type;
			this.linkValue = "";

			this.$emit("input", "");
			this.$nextTick(() => {
				this.focus();
			});
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

.k-link-input-body {
	grid-area: body;
	border-top: 1px solid var(--color-gray-300);
	background: var(--color-gray-100);
	padding: var(--spacing-2);
	padding-bottom: calc(var(--spacing-2) - 1px);
	overflow: auto;
	--tree-color-back: var(--color-gray-100);
	--tree-color-hover-back: var(--color-gray-200);
}
</style>
