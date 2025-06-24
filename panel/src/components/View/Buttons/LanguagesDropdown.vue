<template>
	<div class="k-view-button k-languages-dropdown">
		<k-button
			v-bind="$props"
			:badge="changesBadge"
			:dropdown="true"
			@click="$refs.dropdown.toggle()"
		/>
		<k-dropdown-content
			ref="dropdown"
			:options="$dropdown(options)"
			align-x="end"
		>
			<template #item="{ item: language, index }">
				<k-button
					:key="'item-' + index"
					v-bind="language"
					class="k-dropdown-item k-languages-dropdown-item"
					@click="change(language)"
				>
					{{ language.text }}

					<span
						:data-lock="language.lock"
						class="k-languages-dropdown-item-info"
					>
						<k-icon
							v-if="language.changes"
							:alt="$t('lock.unsaved')"
							:type="language.lock ? 'lock' : 'edit-line'"
							class="k-languages-dropdown-item-icon"
						/>
						<span class="k-languages-dropdown-item-code">
							{{ language.code.toUpperCase() }}
						</span>
					</span>
				</k-button>
			</template>
		</k-dropdown-content>
	</div>
</template>

<script>
import { props as ButtonProps } from "@/components/Navigation/Button.vue";

/**
 * View header button to switch between content languages
 * @displayName LanguagesViewButton
 * @since 4.0.0
 * @unstable
 */
export default {
	mixins: [ButtonProps],
	props: {
		/**
		 * If translations other than the currently-viewed one
		 * have any unsaved changes
		 */
		hasDiff: Boolean,
		options: String
	},
	computed: {
		changesBadge() {
			// `hasDiff` provides the state for all other than the current
			// translation from the backend; for the current translation we need to
			// check `content.diff` as this state can change dynamically without
			// any other backend request that would update `hasDiff`
			if (this.hasDiff || this.$panel.content.hasDiff()) {
				return {
					theme: this.$panel.content.isLocked() ? "red" : "orange"
				};
			}

			return null;
		}
	},
	methods: {
		change(language) {
			this.$reload({
				query: {
					language: language.code
				}
			});
		}
	}
};
</script>

<style>
.k-languages-dropdown-item::after {
	content: "✓";
	padding-inline-start: var(--spacing-1);
}
.k-languages-dropdown-item:not([aria-current="true"])::after {
	visibility: hidden;
}
.k-languages-dropdown-item .k-button-text {
	display: flex;
	flex-grow: 1;
	justify-content: space-between;
	align-items: center;
	gap: var(--spacing-6);
	min-width: 8rem;
}
.k-languages-dropdown-item-info {
	display: flex;
	gap: var(--spacing-2);
	align-items: center;
}
.k-languages-dropdown-item-icon {
	--icon-color: var(--color-orange-500);
	--icon-size: 1rem;
}
.k-languages-dropdown-item-info[data-lock="true"]
	.k-languages-dropdown-item-icon {
	--icon-color: var(--color-red-500);
}
.k-languages-dropdown-item-code {
	font-size: var(--text-xs);
	color: var(--color-gray-500);
}
</style>
