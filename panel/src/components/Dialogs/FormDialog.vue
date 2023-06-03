<template>
	<k-dialog ref="dialog" v-bind="$props" @cancel="cancel" @submit="submit">
		<slot>
			<k-dialog-text v-if="text" :text="text" />
			<k-dialog-fields
				:fields="fields"
				:novalidate="novalidate"
				:value="model"
				@input="input"
				@submit="submit"
			/>
		</slot>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import { props as Fields } from "./Elements/Fields.vue";

export default {
	mixins: [Dialog, Fields],
	props: {
		size: {
			default: "medium",
			type: String
		},
		submitButton: {
			type: [String, Boolean],
			default: () => window.panel.$t("save")
		},
		text: {
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
		}
	}
};
</script>
