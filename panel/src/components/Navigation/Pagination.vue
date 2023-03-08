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
		<k-dropdown v-if="details">
			<k-button v-bind="detailsBtn" />

			<k-dropdown-content
				ref="dropdown"
				align="right"
				class="k-pagination-selector"
				@open="$nextTick(() => $refs.page.focus())"
			>
				<label for="k-pagination-page">
					<span>{{ pageLabel ?? $t("pagination.page") }}:</span>
					<select id="k-pagination-page" ref="page">
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
				<k-button icon="check" @click="goTo($refs.page.value)" />
			</k-dropdown-content>
		</k-dropdown>

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
		isVisible() {
			return this.pages > 1;
		},
		prevBtn() {
			return {
				disabled: this.start <= 1,
				icon: "angle-left",
				size: "xs",
				tooltip: this.prevLabel ?? this.$t("prev"),
				variant: "filled",
				click: () => this.prev()
			};
		},
		nextBtn() {
			return {
				disabled: this.end >= this.total,
				icon: "angle-right",
				size: "xs",
				tooltip: this.nextLabel ?? this.$t("next"),
				variant: "filled",
				click: () => this.next()
			};
		},
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
		start() {
			return (this.current - 1) * this.limit + 1;
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
		pages() {
			return Math.ceil(this.total / this.limit);
		},
		offset() {
			return this.start - 1;
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

				this.$emit("paginate", {
					page: Math.max(1, Math.min(page, this.pages)),
					start: this.start,
					end: this.end,
					limit: this.limit,
					offset: this.offset,
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
.k-pagination {
	display: flex;
	align-items: center;
	user-select: none;
	direction: ltr;
}

.k-pagination-details {
	--button-padding: var(--spacing-3);
	font-size: var(--text-sm);
	white-space: nowrap;
}
.k-pagination-details:has(:not(+ .k-dropdown-content)) {
	cursor: default;
	pointer-events: none;
}

[dir="ltr"] .k-pagination-selector {
	direction: ltr;
}
[dir="rtl"] .k-pagination-selector {
	direction: rtl;
}

.k-pagination-selector {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 0;
	font-size: var(--text-xs);
}
.k-pagination-selector label {
	display: flex;
	border-inline-end: 1px solid rgba(255, 255, 255, 0.35);
	align-items: center;
	padding: var(--spacing-3);
}
.k-pagination-selector select {
	width: auto;
}
.k-pagination-selector label span {
	margin-inline-end: var(--spacing-2);
}
</style>
