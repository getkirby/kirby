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
			<k-stack gap="var(--spacing-3)">
				<k-headline>Error</k-headline>
				<k-box icon="alert" theme="negative">{{ message }}</k-box>
			</k-stack>

			<k-stack gap="var(--spacing-3)">
				<k-headline>Request</k-headline>
				<k-definitions>
					<k-definition term="Path">
						<span>{{ url.pathname }}</span>
					</k-definition>

					<k-definition v-if="url.search" term="Query">
						<span>{{ url.search }}</span>
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
				</k-definitions>
			</k-stack>

			<k-stack gap="var(--spacing-3)">
				<k-stack direction="row" justify="space-between" align="center">
					<k-headline>Exception</k-headline>
					<k-button
						v-if="exception.url"
						:link="exception.url"
						icon="open"
						size="xs"
						variant="filled"
					/>
				</k-stack>
				<k-definitions>
					<k-definition v-if="exception.type" term="Type">
						<k-code-token type="object">{{ exception.type }}</k-code-token>
					</k-definition>

					<k-definition v-if="exception.file" term="File">
						{{ exception.file }}
					</k-definition>

					<k-definition v-if="exception.line" term="Line">
						<k-code-token type="purple">{{ exception.line }}</k-code-token>
					</k-definition>
				</k-definitions>
			</k-stack>

			<k-stack v-if="hasDetails" gap="var(--spacing-3)">
				<k-headline>Details</k-headline>
				<k-code language="js">{{ details }}</k-code>
			</k-stack>

			<k-stack v-if="$panel.debug && trace" gap="var(--spacing-3)">
				<details>
					<summary>Trace</summary>
					<k-error-trace :trace="trace" />
				</details>
			</k-stack>
		</k-stack>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

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
	emits: ["cancel"],
	computed: {
		hasDetails() {
			if (!this.details) {
				return false;
			}

			if (Array.isArray(this.details) === true && this.details.length === 0) {
				return false;
			}

			return true;
		},
		url() {
			return new URL(this.request.url);
		}
	}
};
</script>

<style>
.k-request-error-dialog .k-definitions {
	font-family: var(--font-mono);
	font-size: var(--text-xs);
}
.k-request-error-dialog .k-definitions dt,
.k-request-error-dialog .k-definitions code,
.k-request-error-dialog .k-code {
	font-size: var(--text-xs);
}
.k-request-error-dialog .k-definitions dd {
	min-width: 0;
}
.k-request-error-dialog .k-definitions dd > * {
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
.k-request-error-dialog summary {
	font-weight: var(--font-bold);
	line-height: 1.5em;
}
.k-request-error-dialog summary::marker {
	font-weight: var(--font-normal);
	color: var(--color-text-dimmed);
}
.k-request-error-dialog .k-error-trace {
	margin-top: var(--spacing-3);
}
</style>
