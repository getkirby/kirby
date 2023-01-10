<template>
	<k-field v-bind="$props" :input="_uid" class="k-link-field">
		<div class="k-input k-link-field-container" data-theme="field">
			<div class="k-input-element">
				<div class="k-link-field-header">
					<k-dropdown>
						<k-button
							class="k-link-field-toggle"
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

					<template v-if="currentType.finder">
						<div>{{ value.link }}</div>
					</template>
					<template v-else>
						<input
							class="k-text-input"
							ref="input"
							:placeholder="currentType.placeholder"
							:type="currentType.input"
							:value="value.link"
							@input="currentType.onInput($event.target.value)"
						/>
					</template>
					<template>
						<k-button
							v-if="state === 'settings'"
							class="k-link-field-settings-toggle"
							icon="check"
							@click="state = null"
						/>
						<k-button
							v-else
							class="k-link-field-settings-toggle"
							icon="settings"
							@click="state = 'settings'"
						/>
					</template>
				</div>
			</div>

			<template v-if="state === 'settings'">
				<div class="k-link-field-settings">
					<k-form :fields="settingsFields" :value="value" @input="onSettings" />
				</div>
			</template>

			<template v-else-if="currentType.finder">
				<div class="k-link-field-body">
					<div class="k-link-field-breadcrumb">
						<k-button icon="home" @click="find()" />
						<k-button
							v-for="crumb in finder.crumb"
							:key="crumb.uuid"
							@click="find(crumb.uuid)"
						>
							{{ crumb.title }}
						</k-button>
					</div>
					<div class="k-link-field-finder">
						<ul>
							<!-- <li v-if="finder.parent?.root === false">
								<k-button icon="folder" @click="find(finder.parent?.uuid)">
									..
								</k-button>
							</li> -->
							<li
								v-for="item in finder.children"
								:key="item.uuid"
								:aria-current="item.uuid === value.link"
							>
								<k-button
									icon="angle-right"
									@click="find(item.uuid)"
									:disabled="!item.children"
								/>
								<k-button :icon="item.icon" @click="select(item)">
									{{ item.title }}
								</k-button>
							</li>
						</ul>
					</div>
				</div>
			</template>
		</div>
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
		value: Object
	},
	data() {
		return {
			isLoading: false,
			finder: {},
			memory: {},
			state: null
		};
	},
	created() {
		this.search = this.$helper.debounce(this.search, 250);
		this.find();
	},
	computed: {
		currentType() {
			return this.types[this.value?.type] || this.types["url"];
		},
		settingsFields() {
			return {
				title: {
					label: "Title",
					type: "text",
					icon: "title",
					counter: false,
					width: "1/2"
				},
				target: {
					label: "Target",
					type: "text",
					width: "1/2",
					counter: false
				},
				rel: {
					label: "Rel",
					type: "text",
					counter: false,
					width: "1/2"
				},
				hreflang: {
					label: "Lang",
					type: "text",
					counter: false,
					icon: "globe",
					width: "1/2"
				}
			};
		},
		types() {
			return {
				url: {
					icon: "url",
					label: this.$t("url"),
					placeholder: this.$t("url.placeholder"),
					input: "url",
					scheme: "https://",
					onInput: this.emit
				},
				email: {
					icon: "email",
					label: this.$t("email"),
					placeholder: this.$t("email.placeholder"),
					input: "email",
					scheme: "mailto:",
					onInput: this.emit
				},
				phone: {
					icon: "phone",
					label: "Phone",
					placeholder: "Enter a phone number …",
					input: "tel",
					scheme: "tel:",
					onInput: this.emit
				},
				page: {
					icon: "page",
					label: this.$t("page"),
					placeholder: "Select a page …",
					input: "text",
					scheme: "page://",
					search: "pages",
					onInput: this.search,
					finder: true
				},
				file: {
					icon: "file",
					label: this.$t("file"),
					placeholder: "Search for a file …",
					input: "text",
					scheme: "file://",
					search: "files",
					onInput: this.search,
					finder: true
				}
			};
		}
	},
	watch: {
		"value.type": {
			immediate: true,
			handler(newValue, oldValue) {
				if (newValue === oldValue) {
					return;
				}

				const type = this.value?.type;

				if (["page", "file"].includes(type)) {
					this.find(this.value?.link);
				}
			}
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		},
		emit(link) {
			this.memory[this.value.type] = link;

			this.$emit("input", {
				...this.value,
				link: link
			});
		},
		async find(parent) {
			this.finder = await this.$api.get(this.endpoints.field + "/pages", {
				parent: parent
			});
		},
		findAndSelect(item) {
			if (item) {
				this.find(item.uuid);
				this.select(item);
			} else {
				this.find();
				this.emit("");
			}
		},
		select(item) {
			this.emit(item.uuid);
		},
		onSettings(settings) {
			this.$emit("input", {
				...this.value,
				...settings
			});
		},
		switchType(type) {
			if (type === this.value?.type) {
				return;
			}

			this.finder = {};

			// keep the current value in memory
			this.memory[this.value.type] = this.value.link;

			this.$emit("input", {
				...this.value,
				type: type,
				link: this.memory[type] || ""
			});

			setTimeout(() => {
				this.$refs.input?.focus();
			}, 100);
		}
	}
};
</script>

<style>
.k-link-field-container {
	display: grid;
	grid-template-areas:
		"header"
		"body";
}

.k-link-field-toggle {
	display: flex;
	flex-shrink: 0;
	width: max-content;
	align-items: center;
	font-size: var(--text-sm);
	background: var(--color-gray-200);
	color: var(--color-black);
	padding: 0.325rem 1.325rem 0.325rem 0.325rem;
	line-height: 1;
	border-radius: var(--rounded-sm);
	margin: 0.25rem;
}
.k-link-field-toggle:hover {
	background-color: var(--color-gray-300);
}
.k-link-field-toggle.k-button .k-button-text {
	opacity: 1;
	padding-inline-start: var(--spacing-1);
}
.k-link-field-toggle .k-button-text::after {
	position: absolute;
	top: 50%;
	right: 0.5rem;
	margin-top: -2px;
	content: "";
	border-top: 4px solid currentColor;
	border-inline-start: 4px solid transparent;
	border-inline-end: 4px solid transparent;
}

.k-link-field-header {
	display: grid;
	grid-template-columns: auto 1fr auto;
	align-items: center;
	grid-area: header;
}

.k-link-field-settings-toggle {
	padding: 0.5rem;
}

.k-link-field-body {
	grid-area: body;
	border-top: 1px solid var(--color-gray-300);
	background: var(--color-gray-100);
}

.k-link-field-breadcrumb {
	display: flex;
	align-items: center;
	padding: 0;
	overflow-y: hidden;
	overflow-x: auto;
}
.k-link-field-breadcrumb .k-button {
	display: flex;
	white-space: nowrap;
	padding: 0.5rem 0.625rem;
	line-height: 1;
	align-items: center;
}

.k-link-field-breadcrumb button:not(:last-child)::after {
	position: absolute;
	right: -0.125rem;
	content: "/";
	color: var(--color-gray-300);
}

.k-link-field-finder {
	width: 100%;
	border-top: 1px dashed var(--color-gray-300);
	padding: 0.25rem 0;
	user-select: none;
	max-height: 16rem;
	overflow-x: hidden;
	overflow-y: scroll;
}
.k-link-field-finder li {
	display: flex;
	padding: 0 0.325rem;
	align-items: center;
}
.k-link-field-finder li[aria-current] {
	background: var(--color-blue-200);
}

.k-link-field-finder .k-button {
	padding: 0.25rem 0.325rem;
	text-align: left;
	display: flex;
	align-items: center;
	line-height: 1;
	border-radius: var(--rounded);
}
.k-link-field-finder .k-button:first-child[data-disabled] {
	opacity: 0.05;
}
.k-link-field-finder .k-button:first-child:hover {
	background: rgba(0, 0, 0, 0.075);
}
.k-link-field-finder .k-button:last-child {
	padding-inline: 0.25rem;
	text-align: left;
	display: flex;
	align-items: center;
	line-height: 1;
	border-radius: var(--rounded);
}

.k-link-field-finder .k-button svg {
	width: 14px;
}
.k-link-field-finder li + li {
	margin-top: 1px;
}

.k-link-field-settings {
	padding: 1.5rem;
	background: var(--color-gray-100);
	border-top: 1px solid var(--color-gray-300);
}

.k-link-field-settings .k-field {
	display: grid;
	grid-template-columns: 4rem 1fr;
	gap: 0.75rem;
	align-items: center;
	--field-input-padding: 0.325rem;
	font-size: var(--text-sm);
}
.k-link-field-settings .k-grid {
	gap: 0.25rem 1.5rem;
}
.k-link-field-settings label {
	font-size: var(--text-sm);
	font-weight: 400;
	text-align: right;
	padding-bottom: 0;
}
</style>
