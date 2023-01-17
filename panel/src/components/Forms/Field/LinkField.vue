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
						<div class="k-link-field-preview">
							<k-tag
								v-if="link.length && model"
								:removable="true"
								@click.native="toggleFinder()"
								@remove="remove()"
							>
								<k-item-image :image="model.image" layout="list" />
								{{ model.title }}
							</k-tag>
							<k-button
								v-else
								class="k-link-field-placeholder"
								@click="toggleFinder()"
							>
								{{ currentType.placeholder }}
							</k-button>
						</div>
					</template>
					<template v-else>
						<input
							class="k-text-input"
							ref="input"
							:placeholder="currentType.placeholder"
							:type="currentType.input"
							:value="link"
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
					<k-form
						:fields="settingsFields"
						:value="value || {}"
						@input="onSettings"
					/>
				</div>
			</template>

			<template v-else-if="currentType.finder && state === 'finder'">
				<div class="k-link-field-body">
					<div class="k-link-field-breadcrumb">
						<k-button icon="home" @click="openFinder('')" />
						<k-button
							v-for="crumb in finder.crumb"
							:key="crumb.uuid"
							@click="openFinder(crumb.uuid)"
						>
							{{ crumb.title }}
						</k-button>
					</div>
					<div class="k-link-field-finder">
						<ul>
							<li v-if="finder.parent?.root === false">
								<k-button
									class="k-link-field-finder-item"
									icon="folder"
									@click="openFinder(finder.parent.uuid)"
								>
									..
								</k-button>
							</li>
							<li
								v-for="item in finder.children"
								:key="item.uuid"
								:aria-current="item.uuid === link"
							>
								<k-button
									class="k-link-field-finder-icon"
									:icon="item.icon"
									@click="
										item.type === 'file' ? select(item) : openFinder(item.uuid)
									"
									tabindex="-1"
								/>
								<k-button
									class="k-link-field-finder-item"
									@click="select(item)"
								>
									{{ item.title }}
								</k-button>
								<k-button
									v-if="item.children"
									class="k-link-field-finder-arrow"
									icon="angle-right"
									@click="openFinder(item.uuid)"
								/>
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
			model: null,
			state: null
		};
	},
	created() {
		this.search = this.$helper.debounce(this.search, 250);
	},
	computed: {
		currentType() {
			return this.types[this.linkType] || this.types["url"];
		},
		link() {
			return this.value?.link || "";
		},
		linkType() {
			return this.value?.type || "url";
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
					placeholder: "Select a file …",
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
		link: {
			immediate: true,
			async handler(newValue, oldValue) {
				if (newValue === oldValue || newValue?.length === 0) {
					return;
				}

				if (["page", "file"].includes(this.linkType) === false) {
					return;
				}

				this.model = null;
				this.model = await this.$api.get(this.endpoints.field + "/model", {
					id: newValue,
					type: this.linkType
				});
			}
		},
		linkType() {
			if (this.currentType.finder) {
				this.openFinder();
			}

			this.$nextTick(this.focus);
		}
	},
	methods: {
		emit(link) {
			this.$emit("input", {
				...(this.value || {}),
				link: link
			});
		},
		async find(parent) {
			parent = parent ?? localStorage.getItem("finder");

			this.finder = await this.$api.get(this.endpoints.field + "/finder", {
				parent: parent,
				type: this.linkType
			});

			localStorage.setItem("finder", parent);
		},
		focus() {
			this.$refs.input?.focus();
		},
		onSettings(settings) {
			this.$emit("input", {
				...(this.value || {}),
				...settings
			});
		},
		closeFinder() {
			this.finder = {};
			this.state = null;
		},
		async openFinder(parent) {
			await this.find(parent);
			this.state = "finder";
		},
		remove() {
			this.emit("");
			this.openFinder();
		},
		select(item) {
			if (item.type === "page" && this.linkType === "file") {
				this.find(item.uuid);
				return;
			}

			this.emit(item.uuid);
			this.closeFinder();
		},
		switchType(type) {
			this.$emit("input", {
				...(this.value || {}),
				type: type,
				link: ""
			});
		},
		toggleFinder() {
			if (this.state === "finder") {
				this.closeFinder();
			} else {
				this.openFinder();
			}
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

.k-link-field-header {
	display: grid;
	grid-template-columns: max-content 1fr auto;
	align-items: center;
	gap: 0.25rem;
	height: var(--field-input-height);
	grid-area: header;
}

.k-link-field-toggle.k-button {
	display: flex;
	align-items: center;
	font-size: var(--text-base);
	color: var(--color-black);
	padding: 0 1.325rem 0 0.5rem;
	line-height: 1;
	height: calc(var(--field-input-height) - 4px);
}
.k-link-field-toggle.k-button .k-button-text {
	padding-inline-start: var(--spacing-1);
	font-size: var(--text-base);
	opacity: 1;
	color: var(--field-input-color-before);
}
.k-link-field-toggle.k-button:hover .k-button-text {
	color: var(--color-black);
}

.k-link-field-toggle .k-button-text::after {
	position: absolute;
	top: 50%;
	right: 0.5rem;
	margin-top: -2px;
	content: "";
	border-top: 4px solid var(--color-black);
	border-inline-start: 4px solid transparent;
	border-inline-end: 4px solid transparent;
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
	height: 1.75rem;
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
	justify-content: space-between;
	padding: 0 0.325rem;
	align-items: center;
}
.k-link-field-finder li:hover {
	background: rgba(0, 0, 0, 0.075);
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
.k-link-field-finder-icon:hover,
.k-link-field-finder-arrow:hover {
	background: rgba(0, 0, 0, 0.075);
}
.k-link-field-finder-item {
	padding-inline: 0.25rem;
	flex-grow: 1;
	text-align: left;
	display: flex;
	align-items: center;
	line-height: 1;
	border-radius: var(--rounded);
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

.k-link-field-placeholder.k-button {
	font-size: var(--text-base);
	display: flex;
	color: var(--color-gray-600);
}

.k-link-field-preview {
	display: flex;
	align-items: center;
	padding-left: 0.5rem;
}
.k-link-field-preview .k-tag {
	display: inline-flex;
	background: var(--color-gray-200);
	color: var(--color-black);
	border-radius: var(--rounded-sm);
	overflow: hidden;
}
.k-link-field-preview .k-tag:focus {
	background: var(--color-gray-300);
	color: var(--color-black);
}
.k-link-field-preview .k-tag .k-tag-toggle {
	color: currentColor;
	border-inline-start: 0;
}
.k-link-field-preview .k-tag .k-tag-text {
	display: flex;
	align-items: center;
	gap: 0.75rem;
	padding-inline-start: 0;
	padding-inline-end: 0.25rem;
	padding-block: 0;
	line-height: 1;
}
.k-link-field-preview .k-item-figure {
	height: 1.75rem;
	border-start-start-radius: var(--rounded-sm);
	border-end-start-radius: var(--rounded-sm);
	width: 1.625rem;
}
</style>
