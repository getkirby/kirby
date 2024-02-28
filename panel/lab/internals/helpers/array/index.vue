<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-text>
			Access the following array helpers in your Vue components through
			<code>this.$helper.array</code>
		</k-text>

		<k-lab-example label="$helper.array.fromObject()" script="fromObject">
			<k-text>
				<p>Creates an array from an object:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.array.fromObject(object): array</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{{ fromObjectInput }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.array.fromObject(fromObjectInput)
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.array.search()" script="search">
			<k-text>
				<p>Filters an array by a provided query:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.array.search(array, "{{ searchQuery }}", { min: 2,	field: "name" }): array</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column>
					<k-input
						type="text"
						placeholder="Query"
						@input="searchQuery = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{{ searchInput }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{ searchResult }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.array.sortBy()" script="sortBy">
			<k-text>
				<p>Sorts an array by one or more fields and directions:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.array.sortBy(array, "name desc"): array</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{{ sortInput }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.array.sortBy(sortInput, "name desc")
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.array.split()" script="split">
			<k-text>
				<p>Splits an array into groups by a delimiter entry:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.array.split(array, "-"): array</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{{ splitInput }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.array.split(splitInput, "-")
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.array.wrap()">
			<k-text>
				<p>Wraps a value in an array (ensures the value will be an array):</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.array.wrap(value): array</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">"aaa"</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{ $helper.array.wrap("aaa") }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{{ ["aaa"] }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.array.wrap(["aaa"])
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
/** @script: fromObject */
export const fromObject = {
	computed: {
		fromObjectInput() {
			return { a: 1, b: 2, c: 3 };
		}
	}
};
/** @script-end */

/** @script: search */
export const search = {
	data() {
		return {
			searchQuery: ""
		};
	},
	computed: {
		searchInput() {
			return [
				{ id: 1, name: "John Doe" },
				{ id: 2, name: "Homer Simpson" },
				{ id: 3, name: "Jane Doe" }
			];
		},
		searchResult() {
			return this.$helper.array.search(this.searchInput, this.searchQuery, {
				min: 0,
				field: "name"
			});
		}
	}
};
/** @script-end */

/** @script: sortBy */
export const sortBy = {
	computed: {
		sortInput() {
			return [
				{ id: 1, name: "John Doe" },
				{ id: 2, name: "Homer Simpson" },
				{ id: 3, name: "Jane Doe" }
			];
		}
	}
};
/** @script-end */

/** @script: split */
export const split = {
	computed: {
		splitInput() {
			return [
				{ id: 1, name: "John Doe" },
				{ id: 3, name: "Jane Doe" },
				"-",
				{ id: 2, name: "Homer Simpson" }
			];
		}
	}
};
/** @script-end */

export default {
	mixins: [fromObject, search, sortBy, split]
};
</script>
