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
			@action="$emit('action', $event)"
		>
			<template #item="{ item: language, index }">
				<k-button
					:key="'item-' + index"
					v-bind="language"
					class="k-dropdown-item k-language"
				>
					{{ language.text }} ({{ language.code }})

					<footer class="k-language-footer">
						<span v-if="language.default">
							{{ $t("language.default") }}
						</span>

						<span v-if="language.lock" class="k-language-state k-language-lock">
							<k-icon type="lock" />
							{{ $t("lock.unsaved") }}
						</span>
						<span
							v-else-if="language.changes"
							class="k-language-state k-language-changes"
						>
							<k-icon type="edit" />
							{{ $t("lock.unsaved") }}
						</span>
					</footer>
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
 * @internal
 */
export default {
	mixins: [ButtonProps],
	props: {
		/**
		 * If translations other than the currently-viewed one
		 * have any unsaved changes
		 */
		hasChanges: Boolean,
		options: String
	},
	computed: {
		changesBadge() {
			if (this.hasChanges || this.$panel.content.hasChanges) {
				return {
					theme: "notice"
				};
			}

			return null;
		}
	}
};
</script>

<style>
.k-button.k-language {
	--button-height: auto;
	padding-block: var(--button-padding);
}

.k-language-footer {
	display: flex;
	align-items: center;
	gap: var(--spacing-3);
	font-size: var(--text-xs);
	color: var(--color-gray-400);
	margin-top: var(--spacing-1);
}

.k-language-state {
	--icon-size: 14px;
	display: flex;
	align-items: center;
	gap: var(--spacing-1);
	font-weight: var(--font-bold);
}
.k-language-changes {
	color: var(--color-orange-500);
}
.k-language-lock {
	color: var(--color-red-500);
}
</style>
