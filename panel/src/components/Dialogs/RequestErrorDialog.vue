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
		<k-stack gap="var(--spacing-6)">
			<k-box icon="alert" theme="negative">{{ message }}</k-box>

			<k-stack gap="var(--spacing-3)">
				<k-stack direction="row" justify="space-between" align="center">
					<k-headline>{{ $t("error") }}</k-headline>
					<k-button
						v-if="exception.url"
						:link="exception.url"
						icon="open"
						size="xs"
						variant="filled"
					/>
				</k-stack>
				<k-definitions>
					<k-definition term="URL">
						<k-code-token type="black">
							{{ request.url }}
						</k-code-token>
					</k-definition>

					<k-definition term="Method">
						<k-code-token type="black">
							{{ request.method }}
						</k-code-token>
					</k-definition>

					<k-definition term="Code">
						<k-code-token type="blue">
							{{ response.status }}
						</k-code-token>
					</k-definition>

					<k-definition v-if="exception.type" term="Type">
						<k-code-token type="object">{{ exception.type }}</k-code-token>
					</k-definition>

					<k-definition v-if="exception.file" term="File">
						<k-code-token type="black">{{ exception.file }}</k-code-token>
					</k-definition>

					<k-definition v-if="exception.line" term="Line">
						<k-code-token type="purple">{{ exception.line }}</k-code-token>
					</k-definition>
				</k-definitions>
			</k-stack>

			<k-stack v-if="details" gap="var(--spacing-3)">
				<k-headline>Details</k-headline>
				<k-code language="js">{{ details }}</k-code>
			</k-stack>

			<k-stack v-if="$panel.debug && trace" gap="var(--spacing-3)">
				<k-headline>Trace</k-headline>
				<k-error-trace :trace="trace" />
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
		details: [Array, Object],
		exception: Object,
		message: String,
		request: Object,
		response: Object,
		trace: Array
	},
	emits: ["cancel"]
};
</script>

<style>
.k-request-error-dialog .k-definitions {
	font-family: var(--font-mono);
}
.k-request-error-dialog .k-definitions dt,
.k-request-error-dialog .k-code {
	font-size: var(--text-xs);
}
</style>
