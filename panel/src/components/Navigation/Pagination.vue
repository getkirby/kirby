<template>
	<k-button-group
		v-if="pages > 1"
		layout="collapsed"
		class="k-pagination"
		@keydown.left="prev"
		@keydown.right="next"
	>
		<!-- prev -->
		<k-button
			:disabled="start <= 1"
			:title="$t('prev')"
			class="k-pagination-button"
			icon="angle-left"
			size="xs"
			variant="filled"
			@click="prev"
		/>

		<!-- details -->
		<template v-if="details">
			<k-button
				:disabled="total <= limit"
				:text="total > 1 ? `${detailsText} / ${total}` : total"
				size="xs"
				variant="filled"
				class="k-pagination-details"
				@click="$refs.dropdown.toggle()"
			/>

			<k-dropdown
				ref="dropdown"
				align-x="end"
				class="k-pagination-selector"
				@keydown.left.stop
				@keydown.right.stop
			>
				<form method="dialog" @click.stop @submit="goTo($refs.page.value)">
					<label>
						{{ $t("pagination.page") }}:
						<select ref="page" :autofocus="true">
							<option
								v-for="p in pages"
								:key="p"
								:selected="page === p"
								:value="p"
							>
								{{ p }}
							</option>
						</select>
					</label>
					<k-button type="submit" icon="check" />
				</form>
			</k-dropdown>
		</template>

		<!-- next -->
		<k-button
			:disabled="end >= total"
			:title="$t('next')"
			class="k-pagination-button"
			icon="angle-right"
			size="xs"
			variant="filled"
			@click="next"
		/>
	</k-button-group>
</template>

<script>
/**
 * @example <k-pagination
 *   :details="true"
 *   :page="5"
 *   :total="125"
 *   :limit="10" />
 */
export default {
	props: {
		/**
		 * Show/hide the details display with the page selector
		 * in the center of the two navigation buttons.
		 */
		details: Boolean,
		/**
		 * Sets the limit of items to be shown per page
		 */
		limit: {
			type: Number,
			default: 10
		},
		/**
		 * Sets the current page
		 */
		page: {
			type: Number,
			default: 1
		},
		/**
		 * Sets the total number of items that are in the paginated list.
		 * This has to be set higher to 0 to activate pagination.
		 */
		total: {
			type: Number,
			default: 0
		},
		validate: {
			type: Function,
			default: () => Promise.resolve()
		}
	},
	emits: ["paginate"],
	computed: {
		detailsText() {
			if (this.limit === 1) {
				return this.start;
			}

			return this.start + "-" + this.end;
		},
		end() {
			return Math.min(this.start - 1 + this.limit, this.total);
		},
		offset() {
			return this.start - 1;
		},
		pages() {
			return Math.ceil(this.total / this.limit);
		},
		start() {
			return (this.page - 1) * this.limit + 1;
		}
	},
	methods: {
		/**
		 * Jump to the given page
		 * @public
		 */
		async goTo(page) {
			try {
				await this.validate(page);
				this.$refs.dropdown?.close();

				// Don't assign page directly to `this.page` as
				// this leads to a flicker of the navigation.
				// However, because of this we need to manually
				// calculate start, end and offset that depend on
				// the new page value
				page = Math.max(1, Math.min(page, this.pages));
				const start = (page - 1) * this.limit + 1;

				this.$emit("paginate", {
					page,
					start,
					end: Math.min(start - 1 + this.limit, this.total),
					limit: this.limit,
					offset: start - 1,
					total: this.total
				});
			} catch {
				// pagination stopped
			}
		},
		/**
		 * Go to the previous page
		 * @public
		 */
		prev() {
			this.goTo(this.page - 1);
		},
		/**
		 * Go to the next page
		 * @public
		 */
		next() {
			this.goTo(this.page + 1);
		}
	}
};
</script>

<style>
.k-pagination {
	flex-shrink: 0;
}
.k-pagination-details {
	--button-padding: var(--spacing-3);
	font-size: var(--text-xs);
}
.k-pagination-selector {
	--button-height: var(--height);
	--dropdown-padding: 0;
	overflow: visible;
}
.k-pagination-selector form {
	display: flex;
	align-items: center;
	justify-content: space-between;
}
.k-pagination-selector label {
	display: flex;
	align-items: center;
	gap: var(--spacing-2);
	padding-inline-start: var(--spacing-3);
}
.k-pagination-selector select {
	--height: calc(var(--button-height) - 0.5rem);
	width: auto;
	min-width: var(--height);
	height: var(--height);
	text-align: center;
	background: var(--color-gray-800);
	color: var(--color-white);
	border-radius: var(--rounded-sm);
}
</style>
