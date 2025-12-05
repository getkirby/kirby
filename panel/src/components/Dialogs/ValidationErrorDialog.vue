<template>
	<k-dialog
		ref="dialog"
		:cancel-button="false"
		:submit-button="false"
		:visible="visible"
		class="k-validation-error-dialog"
		size="large"
		@cancel="$emit('cancel')"
	>
		<k-stack>
			<k-box icon="alert" theme="notice">Your changes cannot be saved</k-box>

			<k-stack v-if="details.length" gap="var(--spacing-3)">
				<k-headline>Please, fix the following issues:</k-headline>
				<k-definitions>
					<k-definition
						v-for="(issue, key) in json.details"
						:key="key"
						:term="issue.label"
					>
						<ul class="k-validation-list k-stack">
							<li
								v-for="(message, messageKey) in issue.message"
								:key="messageKey"
							>
								{{ message }}
							</li>
						</ul>
					</k-definition>
				</k-definitions>
			</k-stack>
		</k-stack>
	</k-dialog>
</template>

<script>
import RequestErroDialog from "./RequestErrorDialog.vue";

export default {
	extends: RequestErroDialog,
	emits: ["cancel"]
};
</script>

<style>
.k-validation-error-dialog .k-definitions {
	--definition-term-width: 33%;
	--definition-height: auto;
}
.k-validation-error-dialog .k-definitions dt {
	align-items: baseline;
}
.k-validation-list {
	list-style: disc;
	margin-left: 1rem;
	width: 100%;
	gap: 0.5rem;
}
.k-validation-list li::marker {
	content: "×  ";
	color: var(--color-red-550);
}
</style>
