<template>
	<k-dialog
		ref="dialog"
		:cancel-button="false"
		:submit-button="false"
		:visible="visible"
		class="k-request-error-dialog"
		size="large"
		@cancel="$emit('cancel')"
	>
		<k-stack>
			<k-box icon="alert" theme="negative">{{ instance.message }}</k-box>

			<k-stack gap="var(--spacing-3)">
				<k-stack direction="row" justify="space-between" align="center">
					<k-headline>Error</k-headline>
					<k-button
						v-if="json.editor"
						:link="json.editor"
						icon="open"
						size="xs"
						variant="filled"
					/>
				</k-stack>
				<k-definitions>
					<k-definition term="URL">
						<k-code-token type="black">
							{{ instance.request.url }}
						</k-code-token>
					</k-definition>

					<k-definition term="Method">
						<k-code-token type="black">
							{{ instance.request.method }}
						</k-code-token>
					</k-definition>

					<k-definition term="Code">
						<k-code-token type="blue">
							{{ instance.response.status }}
						</k-code-token>
					</k-definition>

					<k-definition term="Type">
						<k-code-token type="object">{{ json.exception }}</k-code-token>
					</k-definition>

					<k-definition v-if="json.file" term="File">
						<k-code-token type="black">{{ json.file }}</k-code-token>
					</k-definition>

					<k-definition v-if="json.line" term="Line">
						<k-code-token type="purple">{{ json.line }}</k-code-token>
					</k-definition>
				</k-definitions>
			</k-stack>

			<k-stack v-if="details.length" gap="var(--spacing-3)">
				<k-headline>Details</k-headline>
				<k-error-details :details="details" />
			</k-stack>

			<k-stack v-if="$panel.debug && json.trace" gap="var(--spacing-3)">
				<k-headline>Trace</k-headline>
				<k-error-trace :trace="json.trace" />
			</k-stack>
		</k-stack>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import RequestError from "@/errors/RequestError.js";

export default {
	mixins: [Dialog],
	props: {
		instance: RequestError
	},
	emits: ["cancel"],
	computed: {
		json() {
			return this.instance.state();
		},
		details() {
			return this.$helper.array.fromObject(this.json.details);
		}
	}
};
</script>

<style>
.k-request-error-dialog .k-definitions {
	font-family: var(--font-mono);
}
.k-request-error-dialog .k-definitions dt {
	font-size: var(--text-xs);
}
</style>
