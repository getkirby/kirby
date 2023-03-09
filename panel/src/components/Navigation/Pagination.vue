<template>
	<nav v-if="isVisible" :data-align="align" class="k-pagination">
		<!-- prev -->
		<k-button v-bind="prevBtn" />

		<!-- details -->
		<k-dropdown v-if="details">
			<k-button
				:disabled="!hasPages"
				class="k-pagination-details"
				@click="$refs.dropdown?.toggle()"
			>
				<template v-if="total > 1">
					{{ detailsText }}
				</template>
				{{ total }}
			</k-button>

			<k-dropdown-content
				v-if="dropdown"
				ref="dropdown"
				class="k-pagination-selector"
				@open="$nextTick(() => $refs.page.focus())"
			>
				<div class="k-pagination-settings">
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
				</div>
			</k-dropdown-content>
		</k-dropdown>

		<!-- next -->
		<k-button v-bind="nextBtn" />
	</nav>
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
				title: this.prevLabel ?? this.$t("prev"),
				icon: "angle-left",
				click: () => this.prev()
			};
		},
		nextBtn() {
			return {
				disabled: this.end >= this.total,
				title: this.nextLabel ?? this.$t("next"),
				icon: "angle-right",
				click: () => this.next()
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
		hasPages() {
			return this.total > this.limit;
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
	white-space: nowrap;
}
.k-pagination-details:not(:has(+ .k-dropdown-content)) {
	cursor: default;
}

.k-pagination[data-align] {
	text-align: var(--align);
}

.k-dropdown-content.k-pagination-selector {
	position: absolute;
	top: 100%;
	inset-inline-start: 50%;
	transform: translateX(-50%);
	background: var(--color-black);
}
[dir="ltr"] .k-dropdown-content.k-pagination-selector {
	direction: ltr;
}
[dir="rtl"] .k-dropdown-content.k-pagination-selector {
	direction: rtl;
}

.k-pagination-settings {
	display: flex;
	align-items: center;
	justify-content: space-between;
}
.k-pagination-settings label {
	display: flex;
	border-inline-end: 1px solid rgba(255, 255, 255, 0.35);
	align-items: center;
	padding: 0.625rem 1rem;
	font-size: var(--text-xs);
}
.k-pagination-settings label span {
	margin-inline-end: 0.5rem;
}
</style>
