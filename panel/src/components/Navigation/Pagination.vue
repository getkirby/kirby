<template>
	<k-button-group
		v-if="isVisible"
		:data-align="align"
		layout="collapsed"
		class="k-pagination"
	>
		<!-- prev -->
		<k-button v-bind="prevBtn" />

		<!-- details -->
		<template v-if="details">
			<k-button v-bind="detailsBtn" />
			<k-dropdown-content
				ref="dropdown"
				align-x="end"
				class="k-pagination-selector"
			>
				<form method="dialog" @submit="goTo($refs.page.value)">
					<label :for="_uid">{{ pageLabel ?? $t("pagination.page") }}:</label>
					<select :id="_uid" ref="page" :autofocus="true">
						<option
							v-for="p in pages"
							:key="p"
							:selected="page === p"
							:value="p"
						>
							{{ p }}
						</option>
					</select>
					<k-button type="submit" icon="check" />
				</form>
			</k-dropdown-content>
		</template>

		<!-- next -->
		<k-button v-bind="nextBtn" />
	</k-button-group>
</template>

<script>
/**
 * @example <k-pagination
 *   align="center"
 *   :details="true"
 *   :page="5"
 *   :total="125"
 *   :limit="10" />
 */
export default {
	props: {
		/**
		 * The align prop makes it possible to move the pagination component according to the wrapper component.
		 * @values left, center, right
		 */
		align: {
			type: String,
			default: "left"
		},
		/**
		 * Show/hide the details display with the page selector in the center of the two navigation buttons.
		 */
		details: Boolean,
		dropdown: Boolean,
		/**
		 * Enable/disable keyboard navigation
		 */
		keys: Boolean,
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
		 * Sets the label for the page selector
		 */
		pageLabel: String,
		/**
		 * Sets the total number of items that are in the paginated list. This has to be set higher to 0 to activate pagination.
		 */
		total: {
			type: Number,
			default: 0
		},
		/**
		 * Sets the label for the `prev` arrow button
		 */
		prevLabel: String,
		/**
		 * Sets the label for the `next` arrow button
		 */
		nextLabel: String,
		validate: {
			type: Function,
			default: () => Promise.resolve()
		}
	},
	data() {
		return {
			current: this.page
		};
	},
	computed: {
		detailsBtn() {
			return {
				class: "k-pagination-details",
				disabled: this.total <= this.limit,
				size: "xs",
				text: `${this.total > 1 ? this.detailsText : null} ${this.total}`,
				variant: "filled",
				click: () => this.$refs.dropdown?.toggle()
			};
		},
		end() {
			return Math.min(this.start - 1 + this.limit, this.total);
		},
		detailsText() {
			if (this.limit === 1) {
				return this.start + " / ";
			}

			return this.start + "-" + this.end + " / ";
		},
		isVisible() {
			return this.pages > 1;
		},
		nextBtn() {
			return {
				disabled: this.end >= this.total,
				icon: "angle-right",
				size: "xs",
				title: this.nextLabel ?? this.$t("next"),
				variant: "filled",
				click: () => this.next()
			};
		},
		offset() {
			return this.start - 1;
		},
		pages() {
			return Math.ceil(this.total / this.limit);
		},
		prevBtn() {
			return {
				disabled: this.start <= 1,
				icon: "angle-left",
				size: "xs",
				title: this.prevLabel ?? this.$t("prev"),
				variant: "filled",
				click: () => this.prev()
			};
		},
		start() {
			return (this.current - 1) * this.limit + 1;
		}
	},
	watch: {
		page(page) {
			this.current = parseInt(page);
		}
	},
	created() {
		if (this.keys === true) {
			window.addEventListener("keydown", this.onKey, false);
		}
	},
	destroyed() {
		window.removeEventListener("keydown", this.onKey, false);
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

				// Don't assign page directly to `this.current` as
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
			} catch (e) {
				// pagination stopped
			}
		},
		/**
		 * Jump to the previous page
		 * @public
		 */
		prev() {
			this.goTo(this.current - 1);
		},
		/**
		 * Jump to the next page
		 * @public
		 */
		next() {
			this.goTo(this.current + 1);
		},
		onKey(e) {
			switch (e.code) {
				case "ArrowLeft":
					return this.prev();
				case "ArrowRight":
					return this.next();
			}
		}
	}
};
</script>

<style>
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
	padding-inline-start: var(--spacing-3);
	padding-inline-end: var(--spacing-2);
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
