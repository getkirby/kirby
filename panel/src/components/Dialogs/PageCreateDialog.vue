<template>
	<k-dialog
		class="k-page-create-dialog"
		ref="dialog"
		v-bind="$props"
		@cancel="cancel"
		@submit="submit"
	>
		<template slot="container">
			<div class="k-page-create-dialog-sidebar" v-if="blueprints.length > 1">
				<k-headline>Template</k-headline>
				<nav>
					<button
						v-for="blueprint in blueprints"
						:key="blueprint.name"
						:aria-current="blueprint.name === template"
						type="button"
						@click="pick(blueprint.name)"
					>
						{{ blueprint.title }}
					</button>
				</nav>
			</div>
			<div class="k-page-create-dialog-mainbar">
				<k-dialog-notification />
				<k-dialog-body>
					<k-dialog-fields
						:fields="fields"
						:novalidate="novalidate"
						:value="model"
						@input="input"
						@submit="submit"
					/>
				</k-dialog-body>
				<k-dialog-footer>
					<k-dialog-buttons
						:cancel-button="cancelButton"
						:disabled="disabled"
						:icon="icon"
						:submit-button="submitButton"
						:theme="theme"
						@cancel="cancel"
						@submit="submit"
					/>
				</k-dialog-footer>
			</div>
		</template>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import { props as Fields } from "./Elements/Fields.vue";

export default {
	mixins: [Dialog, Fields],
	props: {
		blueprints: {
			type: Array
		},
		parent: {
			type: String
		},
		size: {
			default: "medium",
			type: String
		},
		section: {
			type: String
		},
		submitButton: {
			type: [String, Boolean],
			default: () => window.panel.$t("save")
		},
		template: {
			type: String
		},
		view: {
			type: String
		}
	},
	data() {
		return {
			model: this.value
		};
	},
	watch: {
		value(value) {
			this.model = value;
		}
	},
	methods: {
		input(value) {
			this.model = value;
			this.$panel.dialog.input(value);
		},
		pick(blueprint) {
			this.$panel.dialog.reload({
				query: {
					parent: this.parent,
					section: this.section,
					template: blueprint,
					view: this.view,
					value: this.model
				}
			});
		}
	}
};
</script>

<style>
.k-page-create-dialog-sidebar {
	background: var(--color-dark);
	border-start-start-radius: var(--dialog-rounded);
	border-end-start-radius: var(--dialog-rounded);
	padding: 1.5rem;
	color: var(--color-gray-300);
}
.k-page-create-dialog-sidebar nav {
	display: flex;
	flex-direction: column;
	gap: 2px;
}
.k-page-create-dialog-sidebar .k-headline {
	margin-bottom: 0.75rem;
	line-height: 1.25;
}
.k-page-create-dialog-sidebar button {
	text-align: start;
	padding: 0.5rem;
	font-size: var(--text-sm);
	background: rgba(0, 0, 0, 0.5);
	border-radius: var(--rounded);
}
.k-page-create-dialog-sidebar button[aria-current] {
	outline: 2px solid var(--color-green-300);
}

.k-page-create-dialog:has(.k-page-create-dialog-sidebar) {
	display: grid;
	grid-template-columns: 1fr 2fr;
	background: none;
	--dialog-width: 45rem;
}

.k-page-create-dialog:has(.k-page-create-dialog-sidebar)
	.k-page-create-dialog-mainbar {
	background: var(--color-gray-200);
	border-start-end-radius: var(--dialog-rounded);
	border-end-end-radius: var(--dialog-rounded);
}
</style>
