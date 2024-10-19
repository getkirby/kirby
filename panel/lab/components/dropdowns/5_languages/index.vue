<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="Languages">
			<div class="k-dropdown-content k-languages-dropdown">
				<div class="k-language k-primary-language">
					<k-button class="k-language-button">English (en)</k-button>
					<span class="k-language-info">
						Primary language
						<span class="k-language-changes">
							<k-icon type="edit" /> 3 changes
						</span>
					</span>
				</div>
				<hr />
				<div
					v-for="language in languages"
					:key="language.code"
					:aria-current="language.current"
					:data-disabled="language.disabled"
					class="k-language"
				>
					<k-button class="k-language-button">
						{{ language.name }} ({{ language.code }})
					</k-button>
					<footer class="k-language-footer">
						<k-progress
							class="k-language-progress"
							:style="{
								'--progress-color-value': color(language.progress)
							}"
							:value="language.progress"
						/>

						<span class="k-language-current">✓</span>
					</footer>
					<span class="k-language-info">
						<span v-if="language.disabled" class="k-language-status">
							<k-icon type="hidden" style="--icon-size: 14px" /> disabled
						</span>
						<template v-else> {{ language.progress }}% translated </template>
						<span v-if="language.changes > 0" class="k-language-changes">
							<k-icon :type="language.locked ? 'lock' : 'edit'" />
							{{ language.changes }} changes
						</span>
					</span>
				</div>
			</div>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		languages() {
			return [
				{
					code: "de",
					name: "Deutsch",
					progress: 40,
					changes: 0,
					disabled: false
				},
				{
					code: "fr",
					name: "Français",
					progress: 99,
					changes: 3,
					disabled: false,
					locked: true
				},
				{
					code: "es",
					name: "Español",
					current: true,
					disabled: false,
					progress: 10,
					changes: 1
				},
				{
					code: "it",
					name: "Italiano",
					disabled: false,
					progress: 100,
					changes: 0
				},
				{
					code: "pt",
					name: "Português",
					disabled: true,
					progress: 0,
					changes: 0
				}
			];
		}
	},
	methods: {
		color(progress) {
			if (progress >= 66) return "var(--color-green-600)";
			if (progress > 33 && progress < 66) return "var(--color-yellow-500)";
			return "var(--color-red-500)";
		}
	}
};
</script>

<style>
.k-languages-dropdown {
	position: static;
	display: flex;
	flex-direction: column;
	padding-bottom: var(--spacing-3);
}
.k-language {
	min-width: 18rem;
	padding-inline-end: var(--spacing-1);
}
.k-language[data-disabled="true"] .k-progress {
	opacity: 0.5;
}
.k-language[aria-current="true"] .k-button {
	font-weight: var(--font-bold);
}
.k-primary-language {
	padding-block: var(--spacing-1);
}
.k-language + .k-language {
	margin-top: var(--spacing-3);
}
.k-language-button {
	--button-height: var(--height-xs);
	--button-color-text: var(--dropdown-color-text);
	width: 100%;
	justify-content: start;
	margin-bottom: calc(var(--spacing-1) / 2);
}
.k-language-progress {
	--progress-color-back: var(--color-gray-800);
	--progress-color-value: var(--color-green-600);
}
.k-language-footer {
	display: flex;
	padding-inline: var(--button-padding);
	justify-content: space-between;
	align-items: center;
	gap: var(--spacing-3);
	margin-bottom: var(--spacing-1);
	height: 0.75rem;
}
.k-language-footer .k-toggle-input input {
	top: 0;
}
.k-language-info {
	display: flex;
	align-items: center;
	font-size: var(--text-xs);
	padding-inline: var(--button-padding);
	color: var(--color-gray-400);
	margin-bottom: var(--spacing-1);
}
.k-language-current {
	visibility: hidden;
}
.k-language[aria-current="true"] .k-language-current {
	visibility: visible;
}
.k-language-status {
	display: flex;
	align-items: center;
	gap: var(--spacing-1);
}
.k-language-changes {
	--icon-size: 14px;
	display: flex;
	align-items: center;
	gap: var(--spacing-1);
	color: var(--color-orange-500);
	font-weight: var(--font-bold);
	margin-inline-start: var(--spacing-3);
}
</style>
